<?php

class Page
{
    private $id;
    
    private $titre;
    
    private $idParent;

    private $children = array();

    private $orderPageParent;

    private $orderPageEnfant;


    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function setTitre($titre)
    {
        $this->titre = $titre;
    }

    public function getIdParent()
    {
        return $this->idParent;
    }

    public function setIdParent($idParent)
    {
        $this->idParent = $idParent;
    }

    public function setChildren(array $children)
    {
        $this->children = $children;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getOrderPageParent()
    {
        return $this->orderPageParent;
    }

    public function setOrderPageParent($order)
    {
        $this->orderPageParent = $order;
    }

    public function getOrderPageEnfant()
    {
        return $this->orderPageEnfant;
    }

    public function setOrderPageEnfant($order)
    {
        $this->orderPageEnfant = $order;
    }

}

class Contenu
{
    private $id;
    private $mapUrl;
    private $titre;
    private $paragraphe;
    private $images;
    private $pageId;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function setTitre($titre)
    {
        $this->titre = $titre;
    }

    public function getParagraphe()
    {
        return $this->paragraphe;
    }

    public function setParagraphe($paragraphe)
    {
        $this->paragraphe = $paragraphe;
    }

    public function getImages()
    {
        return $this->images;
    }

    public function setImages($images)
    {
        $this->images = $images;
    }

    public function getPageId()
    {
        return $this->pageId;
    }

    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
    }

    public function getMapUrl()
    {
        return $this->mapUrl;
    }

    public function setMapUrl($mapUrl)
    {
        $this->mapUrl = $mapUrl;
    }
}


?>