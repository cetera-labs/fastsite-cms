<?php
namespace Cetera;
/**
 * Fastsite CMS 3 
 * 
 * Поля и редакторы полей материалов
 *
 * @package FastsiteCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/

$editors = array(
	EDITOR_TEXT_DEFAULT		  => 'editor_text_default',
	EDITOR_TEXT_ALIAS 		  => 'editor_text_alias',
	EDITOR_TEXT_PASSWORD	  => 'editor_text_password',
	EDITOR_TEXT_EMAIL       => 'editor_text_email',
	EDITOR_TEXT_AREA		    => 'editor_text_area',
	EDITOR_TEXT_CKEDITOR   => 'editor_ckeditor',
    EDITOR_TEXT_CKEDITOR_SMALL 	=> 'editor_ckeditor_small',  
	EDITOR_INTEGER_DEFAULT 	=> 'editor_integer_default',
	EDITOR_FILE_DEFAULT 	=> 'editor_file_default',
    EDITOR_FILE_IMAGE 	    => 'editor_file_image',  
	EDITOR_DATETIME_DEFAULT => 'editor_datetime_default',
	EDITOR_BOOLEAN_DEFAULT 	=> 'editor_boolean_default',
	EDITOR_BOOLEAN_RADIO    => 'editor_boolean_radio',
	EDITOR_ENUM_DEFAULT 	=> 'editor_enum_default',
	EDITOR_HIDDEN			=> 'editor_hidden',
	EDITOR_MATSET_DEFAULT 	=> 'editor_matset_default',
	EDITOR_MATSET_FILES		=> 'editor_matset_files',
    EDITOR_MATSET_RICH     	=> 'editor_matset_rich',  
	EDITOR_LINK_DEFAULT   	=> 'editor_link_default',
	EDITOR_LINK_USER        => 'editor_link_user',
	EDITOR_LINK_CATALOG     => 'editor_link_catalog',
	EDITOR_LINKSET_DEFAULT 	=> 'editor_linkset_default',
	EDITOR_LINKSET_EDITABLE => 'editor_linkset_editable',
	EDITOR_LINKSET_CHECKBOX => 'editor_linkset_checkbox',
	EDITOR_LINKSET_USER 	=> 'editor_linkset_user',
	EDITOR_LINKSET2_DEFAULT => 'editor_linkset2_default',
    EDITOR_LINKSET_CATALOG 	=> 'editor_linkset_catalog',  
	EDITOR_MATERIAL_DEFAULT => 'editor_material_default',
	EDITOR_DOUBLE_DEFAULT   => 'editor_double_default',
	EDITOR_TAGS_DEFAULT     => 'editor_tags_default',
    EDITOR_ACE_HTML         => 'editor_ace_html',
    EDITOR_VISUAL_TEMPLATE  => 'editor_visual_template',
);

$field_editors = array(
	FIELD_TEXT		=> array(
						EDITOR_TEXT_DEFAULT,
						EDITOR_TEXT_AREA,
						EDITOR_TEXT_CKEDITOR_SMALL,
                        EDITOR_ACE_HTML,
						EDITOR_TEXT_ALIAS,
						EDITOR_TEXT_PASSWORD,
						EDITOR_TEXT_EMAIL,
						EDITOR_INTEGER_DEFAULT,
						EDITOR_FILE_DEFAULT,
						EDITOR_DATETIME_DEFAULT,
						EDITOR_HIDDEN,
						EDITOR_USER,
	),
	FIELD_LONGTEXT => array(
						EDITOR_TEXT_CKEDITOR,
                        EDITOR_TEXT_CKEDITOR_SMALL,
						EDITOR_TEXT_AREA,
                        EDITOR_ACE_HTML,
						EDITOR_USER,
					   ),
	FIELD_HUGETEXT => array(
						EDITOR_TEXT_CKEDITOR,
                        EDITOR_TEXT_CKEDITOR_SMALL,
						EDITOR_TEXT_AREA,
                        EDITOR_ACE_HTML,
						EDITOR_USER,
    ),
	FIELD_INTEGER => array(
						EDITOR_INTEGER_DEFAULT,
						EDITOR_HIDDEN,
						EDITOR_USER,
	),
	FIELD_DOUBLE    => array(
						EDITOR_DOUBLE_DEFAULT,
						EDITOR_HIDDEN,
						EDITOR_USER,
    ),
	FIELD_FILE		=> array(
						EDITOR_FILE_DEFAULT,
                        EDITOR_FILE_IMAGE,
						EDITOR_USER,
	),  
	FIELD_DATETIME	=> array(
						EDITOR_DATETIME_DEFAULT,
						EDITOR_USER,
	),  
	FIELD_LINK		=> array(
						EDITOR_LINK_DEFAULT,
						EDITOR_HIDDEN,
						EDITOR_USER,
	),  
	FIELD_LINKSET	=> array(
						EDITOR_LINKSET_DEFAULT,
						EDITOR_LINKSET_CHECKBOX,
						EDITOR_LINKSET_EDITABLE,
						EDITOR_USER,
	),  
	FIELD_LINKSET2	=> array(
						EDITOR_LINKSET2_DEFAULT,
	), 	
	FIELD_MATSET	=> array(
						EDITOR_MATSET_DEFAULT,
                        EDITOR_MATSET_RICH,
						EDITOR_USER,
	),  
	FIELD_BOOLEAN	=> array(
						EDITOR_BOOLEAN_DEFAULT,
						EDITOR_BOOLEAN_RADIO,
						EDITOR_USER,
	),  
	FIELD_ENUM		=> array(
						EDITOR_ENUM_DEFAULT,
						EDITOR_USER,
	),  
	FIELD_FORM		=> array(
						EDITOR_LINK_FORM,
						EDITOR_USER,
	),  					   
	PSEUDO_FIELD_FILESET => array(
		//EDITOR_MATSET_FILES,
		//EDITOR_MATSET_IMAGES,
		EDITOR_MATSET_DEFAULT,
		EDITOR_USER,
	),						
	PSEUDO_FIELD_LINK_USER => array(
	    EDITOR_LINK_USER,
		EDITOR_USER,
	),						
	PSEUDO_FIELD_LINKSET_USER => array(
	    EDITOR_LINKSET_USER,
		EDITOR_USER,
	),				
	FIELD_MATERIAL => array(
		EDITOR_MATERIAL_DEFAULT,
		EDITOR_LINK_DEFAULT,
		EDITOR_HIDDEN,
		EDITOR_USER,
	),
	PSEUDO_FIELD_TAGS => array(
		EDITOR_TAGS_DEFAULT,
		EDITOR_LINKSET_DEFAULT,
		EDITOR_LINKSET_EDITABLE,
		EDITOR_USER,
	),
    PSEUDO_FIELD_LINKSET_CATALOG => [
		EDITOR_LINKSET_CATALOG,
		EDITOR_USER,
	],
    PSEUDO_FIELD_LINK_CATALOG => [
		EDITOR_LINK_CATALOG,
		EDITOR_USER,
	],	
    
    PSEUDO_FIELD_WIDGETS => [
        EDITOR_VISUAL_TEMPLATE,
    ]
);
 
$l_field_types[FIELD_TEXT]  		= $translator->_('Текстовый (1-65535 байт)');
$l_field_types[FIELD_LONGTEXT]  	= $translator->_('Большой текст (до 16 Мб)');
$l_field_types[FIELD_HUGETEXT]  	= $translator->_('Огромный текст (до 4 Гб)');
$l_field_types[FIELD_INTEGER]  		= $translator->_('Целочисленный');
$l_field_types[FIELD_DOUBLE]  		= $translator->_('Действительное число');
$l_field_types[FIELD_FILE]  	 	= $translator->_('Файл');
$l_field_types[FIELD_DATETIME]  	= $translator->_('Дата/Время');
$l_field_types[FIELD_LINK]  		= $translator->_('Ссылка на другой материал');
$l_field_types[FIELD_LINKSET]  		= $translator->_('Ссылка на группу материалов');
$l_field_types[FIELD_LINKSET2]      = $translator->_('Ссылка на группу материалов 2');
$l_field_types[FIELD_MATSET]  		= $translator->_('Группа материалов');
$l_field_types[FIELD_BOOLEAN]  		= $translator->_('Логическое');
$l_field_types[FIELD_ENUM] 			= $translator->_('Выбор');
$l_field_types[FIELD_MATERIAL] 		= $translator->_('Материал');

$l_field_types[PSEUDO_FIELD_FILESET]= $translator->_('Набор файлов');
$l_field_types[PSEUDO_FIELD_LINK_USER]= $translator->_('Пользователь');
$l_field_types[PSEUDO_FIELD_LINKSET_USER]= $translator->_('Набор пользователей');
$l_field_types[PSEUDO_FIELD_TAGS]= $translator->_('Ключевые слова');
$l_field_types[PSEUDO_FIELD_LINKSET_CATALOG]= $translator->_('Ссылка на разделы');
$l_field_types[PSEUDO_FIELD_LINK_CATALOG]= $translator->_('Ссылка на раздел');
$l_field_types[PSEUDO_FIELD_WIDGETS]= $translator->_('Коллекция виджетов');

$l_editors = array(
	EDITOR_TEXT_DEFAULT		=> $translator->_('Однострочный редактор текста'),
	EDITOR_INTEGER_DEFAULT 	=> $translator->_('Ввод числовых значений'),
	EDITOR_FILE_DEFAULT 	=> $translator->_('Выбор файла'),
	EDITOR_DATETIME_DEFAULT => $translator->_('Выбор даты/времени'),
	EDITOR_LINK_DEFAULT 	=> $translator->_('Выбор материала из структуры'),
	EDITOR_LINKSET_DEFAULT 	=> $translator->_('Выбор группы материалов'),
	EDITOR_LINKSET_EDITABLE	=> $translator->_('Выбор группы материалов с возможностью редактирования'),
	EDITOR_LINKSET2_DEFAULT => $translator->_('Выбор группы материалов'),
	EDITOR_MATSET_DEFAULT 	=> $translator->_('Группа материалов'),
	EDITOR_BOOLEAN_DEFAULT 	=> $translator->_('Флажок (Checkbox)'),
	EDITOR_BOOLEAN_RADIO 	=> $translator->_('Выбор да/нет (Radio)'),
	EDITOR_ENUM_DEFAULT 	=> $translator->_('Выпадающий список'),
	EDITOR_LINK_FORM 		=> $translator->_('Выбор формы (выпадающий список)'),
	EDITOR_TEXT_ALIAS 		=> $translator->_('Однострочный редактор текста (только лат. и цифр. символы)'),
	EDITOR_TEXT_AREA		=> $translator->_('Многострочный редактор текста'),
    EDITOR_ACE_HTML         => $translator->_('Многострочный редактор c подсветкой HTML'),
	EDITOR_HIDDEN			=> $translator->_('отсутствует'),
	EDITOR_USER				=> $translator->_('Специальный редактор'),
	EDITOR_MATSET_FILES		=> $translator->_('Загрузка/редактирование группы файлов'),
	EDITOR_LINKSET_CHECKBOX => $translator->_('Выбор группы материалов флажками'),
	EDITOR_MATSET_IMAGES	=> $translator->_('Загрузка/редактирование изображений'),
	EDITOR_LINK_USER        => $translator->_('Выбор пользователя'),
	EDITOR_LINKSET_USER 	=> $translator->_('Выбор пользователей'),
	EDITOR_MATERIAL_DEFAULT => $translator->_('Редактирование материала'),
	EDITOR_DOUBLE_DEFAULT   => $translator->_('Редактирование действительных чисел'),
	EDITOR_TAGS_DEFAULT     => $translator->_('Редактирование ключевых слов'),
	EDITOR_TEXT_PASSWORD	=> $translator->_('Пароль'),
	EDITOR_DHTML_CKEDITOR   => $translator->_('CKEditor'),
    EDITOR_LINKSET_CATALOG 	=> $translator->_('Выбор разделов'),
    EDITOR_LINK_CATALOG 	=> $translator->_('Выбор раздела'),
    EDITOR_CKEDITOR_SMALL  	=> $translator->_('CKEditor малый'),
    EDITOR_FILE_IMAGE     	=> $translator->_('Выбор рисунка'),
    EDITOR_MATSET_RICH    	=> $translator->_('Редактируемая группа материалов'),
    EDITOR_VISUAL_TEMPLATE  => $translator->_('Редактор виджетов'),
);

$pseudo_to_original = array(
    PSEUDO_FIELD_WIDGETS => array(
        'original' => FIELD_LONGTEXT
    ),
    PSEUDO_FIELD_FILESET => array(
        'original' => FIELD_MATSET
    ),
    PSEUDO_FIELD_TAGS => array(
        'original' => FIELD_MATSET
    ),  
    PSEUDO_FIELD_LINK_USER => array(
        'original' => FIELD_LINK,
        'len'      => CATALOG_VIRTUAL_USERS
    ),  
    PSEUDO_FIELD_LINKSET_USER => array(
        'original' => FIELD_LINKSET,
        'len'      => CATALOG_VIRTUAL_USERS
    ),
    PSEUDO_FIELD_LINKSET_CATALOG => array(
        'original' => FIELD_LINKSET
    ),     
    PSEUDO_FIELD_LINK_CATALOG => [
        'original' => FIELD_LINK
    ], 	
);