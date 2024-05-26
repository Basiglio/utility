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
$svgs = new FontAwesomeSVG(get_template_directory() . "/assets/svgs");
$svgs->get_svg($svg_name);


