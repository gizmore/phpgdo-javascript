<?php
declare(strict_types=1);
namespace GDO\Javascript;

use GDO\CLI\Process;
use GDO\Core\Module_Core;
use GDO\DB\Database;
use GDO\Util\FileUtil;
use GDO\Util\Strings;
use Throwable;

/**
 * Very basic on-the-fly javascript mangler.
 * Changes are detected by md5.
 *
 * @version 7.0.3
 * @since 4.1.0
 * @author gizmore
 * @see Website
 * @see Module_Javascript
 */
final class MinifyJS
{

	# Binary pathes
	private array $input;

	private string $nodejs;
	private string $uglify;
	private string $annotate;

	private bool $error = false;
	private int $processedSize = 0;

	private array $external = [];
	private array $concatenate = [];

	private bool $skipMinified = false;

	public function __construct(array $javascripts, bool $skipMinified = false)
	{
		$this->input = $javascripts;
		$module = Module_Javascript::instance();
		$this->nodejs = $module->cfgNodeJSPath();
		$this->uglify = $module->cfgUglifyPath();
		$this->annotate = $module->cfgAnnotatePath();
		$this->skipMinified = $skipMinified;
		FileUtil::createDir($this->tempDir());
	}

	public function tempDir(string $path = ''): string { return self::tempDirS($path); }

	public static function tempDirS($path = ''): string { return GDO_PATH . 'assets/' . $path; }

	public static function minified(array $javascripts): array
	{
		$minify = new self($javascripts);
		return $minify->execute();
	}

	public function execute(): array
	{
		# Pass 1 - Early hash
		$earlyhash = $this->earlyHash();
		$earlypath = $this->tempDir("$earlyhash.js");
		if (FileUtil::isFile($earlypath))
		{
			return $this->getOptimizedResult($earlyhash);
		}

		# Pass 2 - Rebuild
		try
		{
			$t = 600; # 10 minutes?
			set_time_limit($t); # may take a while
			Database::instance()->lock('js_minify', $t);

			# After the lock the file is there.
			# So someone else calculated it.
			if (FileUtil::isFile($earlypath))
			{
				return $this->getOptimizedResult($earlyhash);
			}

			# After lock it is still not there... i am the first
			# Minify single files and sort them in concatenate and external
			$minified = array_map([$this, 'minifiedJavascriptPath'], $this->input);

			if ($this->error)
			{
				return $this->input;
			}

			# Build final file
			$finalhash = $this->finalHash();
			$finalpath = $this->tempDir("$finalhash.js");
			if (!FileUtil::isFile($finalpath))
			{
				$concat = implode(' ', $this->concatenate);
				$cat = 'cat';
				if (Process::isWindows())
				{
					$cat = 'type';
					$concat = str_replace('/', '\\', $concat);
				}
				$command = "$cat $concat > $finalpath";
				exec($command);
				if (!(FileUtil::isFile($finalpath)))
				{
					return $minified; # Fail, inbetween version should be ok though.
				}
			}

			# Copy to early access
			copy($finalpath, $earlypath);

			# Abuse external as final loader.
			$this->external[] = GDO_WEB_ROOT . "assets/{$finalhash}.js?vc=" . Module_Core::instance()->cfgAssetVersion()->__toString();
			return $this->external;
		}
		finally
		{
			Database::instance()->unlock('js_minify');
		}
	}

	public function earlyHash(): string { return md5(implode('|', $this->input)); }

	private function getOptimizedResult(string $earlyhash): array
	{
		foreach ($this->input as $path)
		{
			if (
				(strpos($path, '://')) ||
				(strpos($path, '//') === 0) ||
				(strpos($path, GDO_WEB_ROOT . 'index.php?') === 0)
			)
			{
				$this->external[] = $path;
			}
		}
		$this->external[] = GDO_WEB_ROOT . "assets/$earlyhash.js?" . Module_Core::instance()->nocacheVersion();
		return $this->external;
	}

	public function finalHash(): string { return md5(implode('|', array_keys($this->concatenate))); }

	public function minifiedJavascriptPath($path): string
	{
		if (
			(!strpos($path, '://')) &&
			(!str_starts_with($path, GDO_WEB_ROOT . 'index.php')) &&
			(!str_starts_with($path, '//'))
		)
		{
			return $this->minifiedJavascript($path);
		}
		else
		{
			$this->external[] = $path;
			return $path;
		}
	}

	public function minifiedJavascript(string $path): string
	{
		$path = Strings::substrFrom($path, GDO_WEB_ROOT, $path);
		$src = GDO_PATH . Strings::substrTo($path, '?', $path);

		if (FileUtil::isFile($src))
		{
			$this->processedSize += filesize($src);
			$md5 = md5(file_get_contents($src));
			$dest = $this->tempDir("$md5.js");
			if (!FileUtil::isFile($dest))
			{
				if (strpos($src, '.min.js') && $this->skipMinified)
				{
					if (!@copy($src, $dest)) # Skip minified ones
					{
						$this->error = true;
						$this->external[] = $path;
						return $path;
					}
				}
				else
				{
					# Build command
					$annotate = $this->annotate;
					$uglifyjs = $this->uglify;
					$compress = Module_Javascript::instance()->cfgCompressJS() ? '--compress' : '';
					$command = "$annotate -ar $src | $uglifyjs --no-annotations $compress --mangle -o $dest";
					$return = 0;
					$output = [];
					exec($command, $output, $return);
					if ($return != 0)
					{
						$this->error = true;
						$this->external[] = $path;
						return $path; # On error, the original file is left. so you notice.
					}
				}
			}
			$this->concatenate[$md5] = $dest;
			return $dest;
		}
		$this->external[] = $path;
		return $path;
	}

}
