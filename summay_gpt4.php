<?php
// APIキーを設定
$config = require 'config.php';
$OPENAI_API_KEY = $config['api_key'];


// テキストをPOSTリクエストから取得
$text = filter_input(INPUT_POST, 'text', FILTER_SANITIZE_STRING);

if ($text &&  mb_strlen($text) <= 100000) {
    $summary = summarizeText($text, $OPENAI_API_KEY);
    echo $summary;
} else {
    echo "不正な入力文です。";
}




function summarizeText($text, $OPENAI_API_KEY) {

$result = array();

// APIキー
$apiKey = $OPENAI_API_KEY;

//openAI APIエンドポイント
$endpoint = 'https://api.openai.com/v1/chat/completions';

$headers = array(
  'Content-Type: application/json',
  'Authorization: Bearer ' . $apiKey
);

$summary = '<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8" />
    <title>Chat-GPT summary</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<p><a href="https://amid.co.jp/summary/index.html">入力画面へ戻る（要約文は消えます）</a></p>
<button onclick="cp()">copy</button>
<script>
function cp(){
    var txt= document.getElementById("copy2");
    txt.select();
    document.execCommand("Copy");
}
</script>
<textarea id="copy2" readonly>';

$chunks = [];
    $text_length = mb_strlen($text);
    for ($i = 0; $i < $text_length; $i += 2000) {
        $chunks[] = mb_substr($text, $i, 2000);
    }

foreach ($chunks as $chunk) {

// リクエストのペイロード
$data = array(
  'model' => 'gpt-4',
  'messages' => [
    [
    "role" => "system",
    "content" => "あなたはプロの編集者です。文章を200文字程度で日本語で要約してください"
    ],
    [
    "role" => "user",
    "content" => $chunk
    ]
  ]
);

// cURLリクエストを初期化
$ch = curl_init();

// cURLオプションを設定
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// APIにリクエストを送信
$response = curl_exec($ch);

// cURLリクエストを閉じる
curl_close($ch);

// 応答を解析
$result = json_decode($response, true);

// 生成されたテキストを取得
    $summary .= $result['choices'][0]['message']['content'];
    $summary .= '&#13;&#13;';
    }
$summary .= '</textarea>
    </body>
    </html>';

    return $summary;
}
 