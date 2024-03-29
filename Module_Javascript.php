<?php
declare(strict_types=1);
namespace GDO\Javascript;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\Core\GDT_Enum;
use GDO\Core\GDT_Path;
use GDO\Javascript\Method\DetectNode;
use GDO\UI\GDT_Divider;
use GDO\UI\GDT_Link;

/**
 * Configure Javascript options and binaries.
 * Offer minification and obfuscation.
 * Offer javascript debug handler.
 *
 * @version 7.0.3
 * @since 6.10.1
 * @author gizmore
 */
final class Module_Javascript extends GDO_Module
{

	public string $license = 'MIT';
	public int $priority = 10;

	##############
	### Config ###
	##############
	public function getConfig(): array
	{
		return [
			GDT_Divider::make('div_debug'),
			GDT_Checkbox::make('debug_js')->initial('1'),
			GDT_Divider::make('div_minify'),
			GDT_Enum::make('minify_js')->enumValues('no', 'yes', 'concat')->initial('no')->notNull(),
			GDT_Checkbox::make('compress_js')->initial('0')->notNull(),
			GDT_Divider::make('div_binaries'),
			GDT_Link::make('link_node_detect')->href(href('Javascript', 'DetectNode')),
			GDT_Path::make('nodejs_path')->label('nodejs_path')->existingFile(),
			GDT_Path::make('uglifyjs_path')->label('uglifyjs_path')->existingFile(),
			GDT_Path::make('ng_annotate_path')->label('ng_annotate_path')->existingFile(),
		];
	}

	public function onLoadLanguage(): void
	{
		$this->loadLanguage('lang/js');
	}

	public function onInstall(): void
	{
		$detect = DetectNode::make();
		if (!$this->cfgNodeJSPath())
		{
			$detect->detectNodeJS();
		}
		if (!$this->cfgAnnotatePath())
		{
			$detect->detectAnnotate();
		}
		if (!$this->cfgUglifyPath())
		{
			$detect->detectUglify();
		}
	}

	public function cfgNodeJSPath(): ?string { return $this->getConfigVar('nodejs_path'); }

	public function cfgAnnotatePath(): ?string { return $this->getConfigVar('ng_annotate_path'); }

	public function cfgUglifyPath(): ?string { return $this->getConfigVar('uglifyjs_path'); }

	public function onIncludeScripts(): void
	{
		if ($this->cfgDebug())
		{
			$this->addJS('js/gdo-debug.js');
		}
	}

	##############
	### Events ###
	##############

	public function cfgDebug(): string { return $this->getConfigVar('debug_js'); }

	public function cfgMinifyJS(): string { return $this->getConfigVar('minify_js'); }

	public function cfgCompressJS(): string { return $this->getConfigVar('compress_js'); }

}
