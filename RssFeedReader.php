<?php

namespace classes;

/**
 * Class RssFeedReader
 *
 * Represents an RSS feed reader that fetches data from specified feed URLs.
 */
class RssFeedReader
{
    private array $feedUrls;

    public function __construct(array $feedUrls)
    {
        $this->feedUrls = $feedUrls;
    }

    /**
     * Fetches the feed data from multiple RSS feed URLs.
     *
     * @return array The array containing all the feed data from the RSS feed URLs.
     */
    public function fetchFeed(): array
    {
        $allFeedData = array();

        foreach ($this->feedUrls as $feedUrl) {
            $feedData = array();

            $rssContent = file_get_contents($feedUrl);

            if ($rssContent !== false) {
                $rss = simplexml_load_string($rssContent);

                if ($rss !== false) {
                    foreach ($rss->channel->item as $item) {

                        $url = (string)$item->link;
                        $crawler = new Crawler($url);
                        $crawlData = $crawler->crawl();
                        $contentEncoded = $crawlData['content'] ?? '';

                        $feedData[] = array(
                            'title' => (string)$item->title,
                            'link' => (string)$item->link,
                            'date' => (string)$item->pubDate,
                            'description' => (string)$item->description,
                            'content' => $contentEncoded,
                            'category' => (string)$item->category
                        );
                    }
                } else {
                    $feedData[] = "Errore nell'analisi del feed RSS";
                }
            } else {
                $feedData[] = "Errore nel recupero del feed RSS";
            }

            $allFeedData[] = $feedData;
        }

        return $allFeedData;
    }
}
