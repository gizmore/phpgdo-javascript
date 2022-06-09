<?php
namespace GDO\Javascript\Method;

use GDO\Core\GDT_Response;
use GDO\Admin\MethodAdmin;
use GDO\CLI\Process;
use GDO\Javascript\Module_Javascript;
use GDO\Form\GDT_Form;
use GDO\Form\MethodForm;
use GDO\Form\GDT_Submit;
use GDO\UI\GDT_Redirect;

/**
 * Auto-detect nodejs_path, uglifyjs_path and ng_annotate_path.
 * 
 * To install:
 * 
 * $ aptitude install nodejs
 * 
 * $ npm -g install ng-annotate-patched
 * $ npm -g install uglify-js
 * 
 * @author gizmore
 */
final class DetectNode extends MethodForm
{
	use MethodAdmin;
	
	public function getPermission() : ?string { return 'staff'; }
	public function showInSitemap() { return false; }
	public function getMethodTitle() : string { return t('link_node_detect'); }
	
	public function createForm(GDT_Form $form) : void
	{
	    $form->text('info_detect_node_js');
	    $form->actions()->addField(GDT_Submit::make()->onclick([$this, 'executeDetection']));
	}
	
	public function executeDetection()
	{
		$response = $this->detectNodeJS();
		$response->addField($this->detectAnnotate());
		$response->addField($this->detectUglify());
		
		$url = href('Admin', 'Configure', '&module=Javascript');
		$redirect = GDT_Redirect::make()->href($url)->redirectTime(12);
		return $response->addField($redirect);
	}
	
	/**
	 * Detect node/nodejs binary and save to config.
	 * @return GDT_Response
	 */
	public function detectNodeJS()
	{
		$path = null;
		if ($path === null)
		{
			$path = Process::commandPath("nodejs");
		}
		if ($path === null)
		{
		    $path = Process::commandPath("node");
		}
		if ($path === null)
		{
			return $this->error('err_nodejs_not_found');
		}
		Module_Javascript::instance()->saveConfigVar('nodejs_path', $path);
		return $this->message('msg_nodejs_detected', [htmlspecialchars($path)]);
	}
	
	/**
	 * Detect node/nodejs binary and save to config.
	 * @return GDT_Response
	 */
	public function detectAnnotate()
	{
		$path = null;
		if ($path === null)
		{
		    $path = Process::commandPath("ng-annotate-patched", '.cmd');
		}
		if ($path === null)
		{
		    $path = Process::commandPath("ng-annotate", '.cmd');
		}
		if ($path === null)
		{
			return $this->error('err_annotate_not_found');
		}
		Module_Javascript::instance()->saveConfigVar('ng_annotate_path', $path);
		return $this->message('msg_annotate_detected', [htmlspecialchars($path)]);
	}
	
	/**
	 * Detect node/nodejs binary and save to config.
	 * @return GDT_Response
	 */
	public function detectUglify()
	{
		$path = null;
		if ($path === null)
		{
		    $path = Process::commandPath("uglify-js", '.cmd');
		}
		if ($path === null)
		{
		    $path = Process::commandPath("uglifyjs", '.cmd');
		}
		if ($path === null)
		{
		    $path = Process::commandPath("uglify", '.cmd');
		}
		if ($path === null)
		{
			return $this->error('err_uglify_not_found');
		}
		Module_Javascript::instance()->saveConfigVar('uglifyjs_path', $path);
		return $this->message('msg_uglify_detected', [htmlspecialchars($path)]);
	}
   
}
