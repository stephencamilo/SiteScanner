<?php
include 'vendor/autoload.php';
include 'Config.php';

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class SelectorFinder
{
  public function request($path)
  {
    $url = Config::$domain . $path;
    $browser = new HttpBrowser(HttpClient::createForBaseUri($url, [
      'auth_basic' => [
        Config::$basic_auth_user,
        Config::$basic_auth_pass
      ],
    ]));
    $crawler = $browser->request('GET', $url);
    $this->crawl($crawler);
  }

  public function crawl($crawler)
  {
    if (!is_null($crawler)) {
      if (stripos($crawler->filter("body")->text(), Config::$needle) !== false) {
        if (Config::$debug) {
          dump(substr_count($crawler->filter("body")->text(), Config::$needle));
          dump($crawler->filter("body")->getUri());
        }
        file_put_contents("check.txt", $crawler->filter("body")->getUri() . "\n", FILE_APPEND);
      }
    }
  }
}

$sbc_obj = new SelectorFinder;
$paths_json = file_get_contents('paths.json');
$paths = json_decode($paths_json, TRUE);
foreach ($paths as $path) {
  $sbc_obj->request($path);
}
