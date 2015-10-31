<?php

/**
 * Sitemap class
 */
class OPAL_Sitemap {

    /**
     * @var DOMDocument
     */
    private $sitemap;
    /**
     * @var DOMElement
     */
    private $root;
    /**
     * @var boolean
     */
    private $index;
	
	public function __construct($index = false){
        $this->sitemap = new DOMDocument('1.0','UTF-8');
        $this->root = $this->sitemap->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9',($this->index = $index) ? 'sitemapindex' : 'urlset');
	}

    public function addElement($loc,$lastmod = null,$priority = null){
        $element = $this->sitemap->createElement($this->index ? 'sitemap' : 'url');
        $element->appendChild(new DOMElement('loc', $loc));
        if (!is_null($lastmod)){
            $element->appendChild(new DOMElement('lastmod', date(DATE_ATOM,strtotime($lastmod))));
        }
        if (!is_null($priority)){
            $element->appendChild(new DOMElement('priority', $priority));
        }
        $this->root->appendChild($element);
    }

    public function get(){
        $this->sitemap->appendChild($this->root);
        return $this->sitemap->saveXML();
    }
	
}