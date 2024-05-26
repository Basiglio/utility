<?php

namespace classes;

/**
 * Class Crawler
 * retrieve a paragraphs content from single url
 */
class Crawler
{
    private array $visited = array();
    private array $toVisit = array();
    private array $results = array();
    private string $userAgent = "Mozilla/5.0 (compatible; Crawler/1.0;)";

    public function __construct(string $startUrl)
    {
        $this->toVisit[] = $startUrl;
    }

    /**
     * Crawls through a list of URLs.
     *
     * This method starts crawling by visiting URLs from the "toVisit" array. It continues
     * crawling until the "toVisit" array is empty. For each URL, it checks if it is a string.
     * If not, it echoes an error message and continues with the next URL. If the URL is already
     * visited, it skips it and continues with the next URL. If the URL is valid, it fetches the
     * page content using the "fetchPageContent" method. If the page content is not empty, it
     * parses the HTML using the "parseHTML" method. It then adds the URL to the "visited" array,
     * extracts new URLs from the HTML using the "extractUrls" method, and adds the new URLs to
     * the "toVisit" array if they are not already visited or in the "toVisit" array.
     *
     * @return array The results of crawling.
     */
    public function crawl(): array
    {
        while (!empty($this->toVisit)) {

            $url = array_pop($this->toVisit);

            if (!is_string($url)) {
                echo 'Invalid URL: ' . $url . "\n";
                continue;
            }

            if (in_array($url, $this->visited)) {
                continue;
            }

            //echo "Crawling: $url\n";
            $html = $this->fetchPageContent($url);
            if ($html) {
                $this->parseHTML($html);
                $this->visited[] = $url;

                $newUrls = $this->extractUrls($html, $url);
                foreach ($newUrls as $newUrl) {
                    if (!in_array($newUrl, $this->visited) && !in_array($newUrl, $this->toVisit)) {
                        $this->toVisit[] = $newUrl;
                    }
                }
            }
        }

        return $this->results;
    }

    /**
     * Fetches the content of a web page.
     *
     * @param string $url The URL of the web page
     * @return bool|string The content of the web page if successful, empty string otherwise
     */
    private function fetchPageContent(string $url): bool|string
    {
        if (!is_string($url)) {
            echo 'Invalid URL: ' . $url . "\n";
            return '';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);

        $data = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch) . "\n";
        }

        curl_close($ch);

        return $data ?: '';
    }


    /**
     * Parses HTML content and extracts paragraph elements.
     *
     * @param string $html The HTML content to be parsed
     * @return void
     */
    private function parseHTML(string $html): void
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);

        $xpath = new \DOMXPath($dom);

        $content = '';
        $nodes = $xpath->query('//p');
        foreach ($nodes as $node) {
            $content .= $node->nodeValue . "\n";
        }
        $this->results['content'] = $content;
    }


    /**
     * Extracts URLs from HTML content.
     *
     * @param string $html The HTML content to extract URLs from
     * @param string $baseUrl The base URL to filter URLs by
     * @return array An array of URLs found in the HTML content
     */
    private function extractUrls(string $html, string $baseUrl): array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);

        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->query('//a[@href]');
        $urls = array();

        foreach ($nodes as $node) {
            $href = $node->getAttribute('href');
            if (filter_var($href, FILTER_VALIDATE_URL) && strpos($href, $baseUrl) === 0) {
                $urls[] = $href;
                echo "Found URL: " . $href . "\n";
            }
        }

        return $urls;
    }

}
