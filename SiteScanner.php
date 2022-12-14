<?php
include 'vendor/autoload.php';
include 'Config.php';

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class SiteScanner
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
      if (!is_file(Config::$path_file_name)) {
        file_put_contents(Config::$path_file_name, "[]");
      }
      $paths_json = file_get_contents(Config::$path_file_name);
      $paths = json_decode($paths_json, TRUE);
      $crawler->filter('a')->each(function (Crawler $anchor, $i) {
        $path = $anchor->attr('href');

        $starts_w_http = stripos($path, 'http:') !== 0;
        $starts_w_mail = stripos($path, 'mailto:') !== 0;
        $starts_w_tel = stripos($path, 'tel:') !== 0;
        $starts_w_https = stripos($path, 'https:') !== 0;
        $starts_w_qm = stripos($path, '?') !== 0;
        $has_hash = stripos($path, '#') === false;
        $is_pdf = stripos($path, '.pdf') === false;

        $url_is_valid = $starts_w_https && $starts_w_http && $starts_w_tel && $starts_w_mail && $has_hash && $is_pdf && $starts_w_qm;
        if ($url_is_valid) {
          $starts_w_slash = stripos($path, '/') === 0;
          $path_treated =  '/' . $path;
          if ($starts_w_slash) {
            $path_treated = $path;
          }
          $paths_json = file_get_contents(Config::$path_file_name);
          $paths = json_decode($paths_json, TRUE);
          sort($paths);
          if (!in_array($path_treated, $paths)) {
            $paths[] = $path_treated;
            $paths_json = json_encode($paths);
            file_put_contents(Config::$path_file_name, $paths_json);
            $crawler_inner = $this->request($path_treated);
            $this->crawl($crawler_inner);
          }
        }
      });
    }
  }
}

$sbc_obj = new SiteScanner;
$sbc_obj_crawler = $sbc_obj->request('/');
