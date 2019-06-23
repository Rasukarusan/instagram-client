<?php
$request = json_decode(file_get_contents('php://input'), true);
$urls = $request['URLs'];
if(empty($urls)) exit;

foreach ($urls as $url) {
    if(empty($url)) continue;

    $json = getJsonByUrl($url);
    if(empty($json)) {
        echo returnFatalResult();
        continue;
    }

    $username = getUsername($json);
    $post_text = getPostText($json);
    $image_url = getImageUrl($json);

    if(empty($username) || empty($post_text) || empty($image_url)) {
        echo returnFatalResult();
        continue;
    }

    $result = [[
        'Username' => $username,
        'ImageURL' => $image_url,
        'PostText' => $post_text,
        'OrgURL'   => $url,
        'Err'   => '',
    ]];
    echo json_encode($result);
}

return;

/**
 * 失敗用の結果を返す
 */
function returnFatalResult() : string {
    $result = [[
        'Username' => '',
        'ImageURL' => '',
        'PostText' => '',
        'OrgURL'   => '',
        'Err'      => '取得できませんでした',
    ]];
    return json_encode($result);
}

/**
 * curlでGETリクエストを実行
 */
function fetch(string $url) : string {
    $ch = curl_init();
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ];
    curl_setopt_array($ch, $options);
    $resp = curl_exec($ch);
    curl_close($ch);
    return $resp;
}

/**
 * InstagramにcurlしてJSONを取得する
 *
 * @return mixed(string|Object) 取得に失敗した場合空文字を返す
 */
function getJsonByUrl($url) {
    $url = preg_replace('/\n|\r|\r\n/', '', $url);
    $curl_result = fetch($url);

    if(is_null($curl_result)) {
        return '';
    }
    preg_match_all('/window._sharedData.*{.*}/', $curl_result, $match);
    if(is_null($match[0][0])) return '';
    $json_str = str_replace('window._sharedData = ','',$match[0][0]);
    if(empty($json_str)) {
        return '';
    }
    return json_decode($json_str);
}

function getImageUrl($json) : string {
    if(is_null($json->entry_data->PostPage)) {
        return '';
    }
    return $json->entry_data->PostPage[0]->graphql->shortcode_media->display_url;
}

function getPostText($json) : string {
    if(is_null($json->entry_data->PostPage)) {
        return '';
    }
    return $json->entry_data->PostPage[0]->graphql->shortcode_media->edge_media_to_caption->edges[0]->node->text;
}

function getUsername($json) : string {
    if(is_null($json->entry_data->PostPage)) {
        return '';
    }
    return $json->entry_data->PostPage[0]->graphql->shortcode_media->owner->username;
}
