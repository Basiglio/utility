# utility
all my utility classes

Autoinclude - autoinclude scripts from anything folder
example:
$autoInclude = new AutoInclude(get_template_directory().'/resources/inc/');
$autoInclude->performInclude();

AddFeaturedImages - add featured images
example:
$extra_featured_image = new AddFeaturedImages($post_type,$metabox_title,$identifier);

SvgRender - render svg from project folder
example:
$svgs = new SvgRender(get_template_directory() . "/assets/svgs");
$svgs->get_svg($svg_name);

Logger - create a log at specific path
example:
$logger = new Logger('logs', 'app'.date('Y-m-d H:i:s').'.log');
$logger->logMessage('lanciata retrieve_posts_cron_job');

Crawler - crawl all <p> tag in a webpage
example:
$crawler = new Crawler($url);
$crawlData = $crawler->crawl();

RssFeedReader - read and websites rss feeds
example:
$reader = new RssFeedReader($feedUrls);
$feedData = $reader->fetchFeed();

GptContentEleborator - read and manipulate text content with openAI api
example:
$contentProcessor = new GptContentElaborator($gptApiKey, $gptEndpoint, $logger);
$processedData = $contentProcessor->processContent($feedData);

DalleImageGenerator - create an image from text
example:
$dalle_generator = new DalleImageGenerator($apiKey,$logger,$dalleEndpoint);
$prompt = wp_strip_all_tags($item['title']);
$response = $dalle_generator->generateImage($prompt);

WpPostCreator - create programmatically posts in wordpress
$postCreator = new WpPostCreator($logger, $gptApiKey, $dalleEndpoint);
$postCreator->createPosts($processedData);


