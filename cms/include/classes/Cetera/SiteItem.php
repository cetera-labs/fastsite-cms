<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
namespace Cetera;

/**
 * Интерфейс "Часть сайта". Все элементы сайта: серверы, разделы, материалы должны его реализовывать
 *  
 * @package CeteraCMS
 */ 
interface SiteItem {

    /**
     * Возвращает абсолютный URL элемента (/раздел/элемент)
     *           
     * @return string
     */ 
    public function getUrl();
    
    /**
     * Возвращает полный URL элемента (http://сервер/раздел/элемент)
     *           
     * @return string
     */ 
    public function getFullUrl();
}