<?php

namespace classes;

use DOMException;
use Exception;

class SvgRender
{

    public string $svg_dir;

    /**
     * @throws Exception
     */
    public function __construct($svg_dir)
    {
        if (!is_dir($svg_dir)) {
            throw new Exception("Directory $svg_dir does not exist");
        }

        $this->svg_dir = $svg_dir;
    }


    /**
     * Get SVG icon
     *
     * @param string $id icon ID e.g. fas fa-house
     * @param array $opts options array
     * @return string|boolean
     * @throws DOMException
     */
    public function get_svg(string $id, array $opts = []): bool|string
    {
        try {
            $icon = $this->get_icon_details($id);
        } catch (Exception $e) {
            return false;
        }

        $doc = new \DOMDocument();
        $doc->load($icon['filepath']);

        $default_opts = [
            'title' => false,
            'class' => false,
            'default_class' => true,
            'inline_style' => true,
            'role' => 'img',
            'fill' => 'currentColor',
        ];

        $opts = array_merge($default_opts, $opts);


        $classes = '';
        if ($opts['default_class']) {
            $classes .= 'svg-inline--fa';
        }
        if ($opts['class']) {
            $classes .= ' ' . $opts['class'];
        }

        $aria_opts = array_filter($opts, function ($item, $key) {
            if (str_starts_with($key, 'aria-')) return $item;
        }, ARRAY_FILTER_USE_BOTH);


        foreach ($doc->getElementsByTagName('svg') as $item) {
            if ($classes != '') $item->setAttribute('class', $classes);
            if ($opts['role']) $item->setAttribute('role', $opts['role']);


            foreach ($aria_opts as $key => $val) {
                $item->setAttribute($key, $val);
            }


            if ($opts['title']) {
                $title = $doc->createElement("title");
                $title->nodeValue = $opts['title'];

                $title_node = $item->appendChild($title);

                // <title> id attribute has to be set to add aria-labelledby
                if (isset($opts['title_id'])) {
                    $title_id = $opts['title_id'];
                    $title_node->setAttribute('id', $title_id);
                    $item->setAttribute('aria-labelledby', $title_id);
                }

            }


            if (!isset($aria_opts['aria-label']) && !isset($opts['title_id'])) {
                $item->setAttribute('aria-hidden', 'true');
            }
        }


        foreach ($doc->getElementsByTagName('path') as $item) {
            $fill = $opts['fill'];
            $opacity = false;

            // duotone
            switch ($item->getAttribute('class')) {
                case 'fa-primary':
                    if (isset($opts['primary']['fill'])) $fill = $opts['primary']['fill'];
                    if (isset($opts['primary']['opacity'])) $opacity = $opts['primary']['opacity'];
                    break;


                case 'fa-secondary':
                    if (isset($opts['secondary']['fill'])) $fill = $opts['secondary']['fill'];
                    if (isset($opts['secondary']['opacity'])) $opacity = $opts['secondary']['opacity'];
                    break;
            }


            $item->setAttribute('fill', $fill);
            if ($opacity) $item->setAttribute('opacity', $opacity);
        }


        // duotone styles
        if ($opts['inline_style']) {
            $styles = [];

            if (isset($opts['primary']['fill'])) {
                $styles[] = '--fa-primary-color:' . $opts['primary']['fill'];
            }

            if (isset($opts['primary']['opacity'])) {
                $styles[] = '--fa-primary-opacity:' . $opts['primary']['opacity'];
            }


            if (isset($opts['secondary']['fill'])) {
                $styles[] = '--fa-secondary-color:' . $opts['secondary']['fill'];
            }

            if (isset($opts['secondary']['opacity'])) {
                $styles[] = '--fa-secondary-opacity:' . $opts['secondary']['opacity'];
            }


            if (empty($styles) || !isset($opts['primary']['fill'], $opts['secondary']['fill'])) {
                $styles[] = 'color:' . $opts['fill'];
            }


            if ($styles) {
                foreach ($doc->getElementsByTagName('svg') as $svg) {
                    $svg->setAttribute('style', implode(';', $styles));
                }
            }
        }


        return $doc->saveHTML();
    }


    /**
     * Get an icon's details from icon ID
     *
     * @param string $id icon ID e.g. fas fa-house
     * @return array
     * @throws Exception
     */
    public function get_icon_details(string $id): array
    {
        $icon = array();

        $filename = $this->get_icon_filename($id);

        $icon['filename'] = $filename;
        $icon['filepath'] = str_replace('/', DIRECTORY_SEPARATOR, "$this->svg_dir/$filename.svg");
        
        if (!is_file($icon['filepath'])) {
            throw new Exception('File ' . $icon['filepath'] . ' does not exist.');
        }

        return $icon;
    }

    /**
     * Get the icon's SVG file name
     *
     * @param string $icon_name
     * @return string
     */
    public function get_icon_filename(string $icon_name): string
    {
        return str_replace('fa-', '', $icon_name);
    }
}
