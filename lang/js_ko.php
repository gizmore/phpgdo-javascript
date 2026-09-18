<?php
namespace GDO\Javascript\lang;

return [
	'cfg_minify_js' => '자바스크립트 축소 모드',
	'cfg_compress_js' => '자바스크립트 축소 압축',
	'cfg_nodejs_path' => 'nodejs의 경로',
	'cfg_uglifyjs_path' => 'uglify-js 경로',
	'cfg_ng_annotate_path' => 'ng-annotate 경로',
	'cfg_link_node_detect' => '자바스크립트 바이너리 검색',
	'link_node_detect' => '자바스크립트 바이너리 감지…',
	'msg_nodejs_detected' => 'nodejs 바이너리가 감지되었습니다: %s',
	'msg_annotate_detected' => 'ng-annotate 바이너리가 감지되었습니다: %s',
	'msg_uglify_detected' => 'uglify-js 바이너리가 발견되었습니다: %s',
	'err_nodejs_not_found' => 'nodejs를 찾을 수 없습니다',
	'err_annotate_not_found' => 'ng-annotate를 찾을 수 없습니다',
	'err_uglify_not_found' => 'uglify-js를 찾을 수 없습니다',
	'enum_concat' => '축소 및 병합',
	'info_detect_node_js' => '설치된 js 바이너리를 감지합니다.<br/>
이 방법을 실행하기 <em>전에</em> 이를 설치하세요.<br/>
<br/>
apt-get 설치 nodejs<br/>
npm 설치 uglify-js -g<br/>
npm install -g ng-annotate-patched<br/>',
	'mailb_js_error' => '자바스크립트 오류가 발생했습니다:<br/>
<br/>
URL: %s<br/>
<br/>
메시지: %s<br/>
----------
<예비>
%s</pre><br/>
<br/>
감사합니다<br/>
%s 시스템<br/>',
];
