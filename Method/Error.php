<?php
namespace GDO\Javascript\Method;

use GDO\Core\GDT;
use GDO\Core\MethodAjax;
use GDO\Core\GDT_String;
use GDO\Core\GDT_Text;
use GDO\Mail\Mail;

/**
 * Triggered by JS to send js error mails.
 * 
 * @TODO: There is a possible exploit lurking, if your mail client renders html.
 * 
 * @author gizmore
 * @version 7.0.0
 * @since 6.11.1
 */
final class Error extends MethodAjax
{
	public function gdoParameters() : array
	{
		return [
			GDT_String::make('url'),
			GDT_String::make('message'),
			GDT_Text::make('stack'),
		];
	}
	
	public function execute() : GDT
	{
		if (GDO_ERROR_MAIL)
		{
			$url = $this->gdoParameterVar('url');
			$message = $this->gdoParameterVar('message');
			$stack = $this->gdoParameterVar('stack');
			$stack = "<pre>{$stack}</pre>";
			$message = tiso(GDO_LANGUAGE, 'mailb_js_error', [
				$url, $message, $stack, sitename()]);
			Mail::sendDebugMail(': JS Error', $message);
		}
	}
	
}
