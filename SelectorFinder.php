<?php
include 'vendor/autoload.php';

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class SelectorFinder
{
  public function request($path)
  {
    $url = $this->domain . $path;
    $browser = new HttpBrowser(HttpClient::createForBaseUri($url, [
      'auth_basic' => [
        $this->basic_auth_user,
        $this->basic_auth_pass
      ],
    ]));
    $crawler = $browser->request('GET', $url);
    $this->crawl($crawler);
  }

  public function crawl($crawler)
  {
    if (!is_null($crawler)) {
      if ($crawler->filter($this->css_selector)->count() > 0) {
        if ($this->debug) {
          dump($crawler->filter($this->css_selector)->count());
          dump($crawler->filter($this->css_selector)->getUri());
        }
        file_put_contents("check.txt", $crawler->filter($this->css_selector)->getUri() . "\n", FILE_APPEND);
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
