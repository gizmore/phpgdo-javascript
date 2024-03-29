<?php
namespace GDO\Javascript\Method;

use GDO\Core\GDT;
use GDO\Core\GDT_Response;
use GDO\Core\GDT_String;
use GDO\Core\GDT_Text;
use GDO\Core\MethodAjax;
use GDO\Mail\Mail;

/**
 * Triggered by JS to send js error mails.
 *
 * @TODO: There is a possible exploit lurking, if your mail client renders html.
 *
 * @version 7.0.0
 * @since 6.11.1
 * @author gizmore
 */
final class Error extends MethodAjax
{

	public function gdoParameters(): array
	{
		return [
			GDT_String::make('url'),
			GDT_String::make('message')->max(768),
			GDT_Text::make('stack'),
		];
	}

	public function execute(): GDT
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
		return GDT_Response::make();
	}

}
