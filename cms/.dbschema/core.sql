TRUNCATE TABLE dir_structure;
INSERT INTO dir_structure VALUES (1,0,1,4,0);
INSERT INTO dir_structure VALUES (2,1,2,3,1);

TRUNCATE TABLE types;
INSERT INTO `types` 
(`id`, `alias`, `describ`, `fixed`)
VALUES
(1,'materials', 'Materials[ru=Материалы]',1),
(2,'users',     'Users[ru=Пользователи]',1),
(4,'dir_data',  'Sections[ru=Разделы]',1);

# --------------------------------------------------------

TRUNCATE TABLE types_fields;

INSERT INTO `types_fields` 
(`field_id`, `id`, `name`, `type`, `describ`, `len`, `fixed`, `required`, `shw`, `tag`, `pseudo_type`, `default_value`, `editor`, `editor_user`, `page`) VALUES
(1, 1, 'name', 1,              'Title[ru=Заголовок]',        1024, 1, 0, 1, 2,  0, NULL, 0, NULL, NULL),
(8, 1, 'alias', 1,             'Alias',            255,  1, 1, 1, 3,  0, NULL, 0, NULL, NULL),
(7, 1, 'autor', 6,             'Author[ru=Автор]',            -1,   1, 1, 0, 4,  1003, NULL, 0, NULL, NULL),
(4, 1, 'dat_update', 5,        'Edit date[ru=Дата редактирования]',1,    1, 1, 0, 5,  0, NULL, 0, NULL, NULL),
(3, 1, 'dat', 5,               'Creation date[ru=Дата создания]',    1,    1, 1, 0, 6,  0, NULL, 0, NULL, NULL),
(2, 1, 'idcat', 6,             'Section[ru=Раздел]',           0,    1, 1, 0, 7,  1008, NULL, 0, NULL, NULL),
(5, 1, 'tag', 3,               'Sort[ru=Сортировка]',       1,    1, 0, 1, 1,  0, 100, 0, NULL, NULL),
(6, 1, 'type', 3,              '',                 1,    1, 1, 0, 8,  0, NULL, 0, NULL, NULL),
(9, 1, 'meta_title', 1,        'Meta title',       1000, 0, 0, 1, 13, 0, '', 1, '', 'SEO'),
(10, 1, 'meta_keywords', 1,    'Meta keywords',    1000, 0, 0, 1, 14, 0, '', 16, '', 'SEO'),
(11, 1, 'meta_description', 1, 'Meta description', 1000, 0, 0, 1, 15, 0, '', 16, '', 'SEO'),
(12, 1, 'text', 2,             'Edit[ru=Редактирование]',   1,    0, 0, 1, 16, 0, '', 29, '', ''),
(14, 1, 'short', 1,            'Lead[ru=Вводная]',          1000, 0, 0, 1, 18, 0, '', 33, '', NULL),
(15, 1, 'pic', 4,              'Picture[ru=Картинка]',         1,    0, 0, 1, 19, 0, '', 32, '', NULL),

(21, 4, 'meta_title', 1,       'Meta title', 1000, 0, 0, 1, 11, 0, '', 1, '', 'SEO'),
(22, 4, 'meta_keywords', 1,    'Meta keywords', 1000, 0, 0, 1, 12, 0, '', 1, '', 'SEO'),
(23, 4, 'meta_description', 1, 'Meta description', 1000, 0, 0, 1, 13, 0, '', 16, '', 'SEO'),
(24, 4, 'pic', 4,              'Picture[ru=Картинка]', 1, 0, 0, 1, 14, 0, '', 4, '', NULL),

(30, 2, 'login', 1,      'Login', 255, 1, 1, 1, 30, 0, NULL, 0, NULL, NULL),
(31, 2, 'name', 1,       'Name[ru=Имя]', 255, 1, 0, 1, 31, 0, NULL, 0, NULL, NULL),
(32, 2, 'password', 1,   'Password', 32, 1, 1, 1, 32, 0, NULL, 27, NULL, NULL),
(33, 2, 'email', 1,      'E-mail', 255, 1, 0, 1, 33, 0, NULL, 28, NULL, NULL),
(34, 2, 'describ', 1,    'Description[ru=Описание]', 1000, 1, 0, 1, 34, 0, NULL, 15, NULL, NULL),
(35, 2, 'date_reg', 5,   'Registration date[ru=Дата регистрации]', 64, 1, 1, 0, 35, 0, NULL, 0, NULL, NULL),
(36, 2, 'last_login', 5, 'Last login[ru=Дата входа]', 100, 1, 0, 0, 36, 0, NULL, 0, NULL, NULL),
(37, 2, 'disabled', 9,   'Disabled[ru=Заблокирован]', 1, 1, 0, 1, 37, 0, NULL, 0, NULL, NULL),
(40, 2, 'phone', 1,      'Phone[ru=Телефон]', 100, 1, 0, 1, 40, 0, '', 1, '', NULL);
# --------------------------------------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `password` char(32) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `describ` varchar(1000) DEFAULT NULL,
  `date_reg` datetime DEFAULT NULL,   
  `last_login` datetime DEFAULT NULL,
  `disabled` tinyint(3) NOT NULL DEFAULT '0',
  `phone` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
);

INSERT INTO users (id,login,name,disabled) VALUES (0,'nobody','nobody',1);

INSERT INTO users_groups_allow_cat SET group_id=-2, catalog_id=0, permission=5;
INSERT INTO users_groups_allow_cat SET group_id=-2, catalog_id=0, permission=7;
INSERT INTO users_groups_allow_cat SET group_id=-2, catalog_id=0, permission=8;
INSERT INTO users_groups_allow_cat SET group_id=-2, catalog_id=0, permission=9;

DROP TABLE IF EXISTS `materials`;
CREATE TABLE `materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idcat` int(11) DEFAULT NULL,
  `dat` datetime DEFAULT NULL,
  `dat_update` datetime DEFAULT NULL,
  `name` varchar(2048) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `autor` int(11) NOT NULL DEFAULT '0',
  `tag` int(11) NOT NULL DEFAULT '1',
  `alias` varchar(255) NOT NULL,
  `text` text,
  `short` varchar(1000) DEFAULT NULL,
  `pic` varchar(1024) DEFAULT NULL,
  `meta_title` varchar(1000) DEFAULT NULL,
  `meta_keywords` varchar(1000) DEFAULT NULL,
  `meta_description` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idcat` (`idcat`),
  KEY `dat` (`dat`),
  KEY `alias` (`alias`)
);

DROP TABLE IF EXISTS `dir_data`;
CREATE TABLE IF NOT EXISTS `dir_data` (
  `id` mediumint(11) unsigned NOT NULL AUTO_INCREMENT,
  `tag` smallint(5) unsigned NOT NULL DEFAULT '1',
  `name` varchar(255) DEFAULT NULL,
  `tablename` varchar(64) DEFAULT NULL,
  `type` int(11) unsigned NOT NULL DEFAULT '0',
  `template` varchar(250) DEFAULT NULL,
  `typ` int(11) NOT NULL DEFAULT '0',
  `dat` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `hidden` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_server` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `templateDir` varchar(100) DEFAULT NULL,
  `inheritFields` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `pic` varchar(1024) DEFAULT NULL,
  `meta_title` varchar(1000) DEFAULT NULL,
  `meta_keywords` varchar(1000) DEFAULT NULL,
  `meta_description` varchar(1000) DEFAULT NULL,  
  PRIMARY KEY (`id`)
);

INSERT INTO dir_data VALUES ( 1, 1, 'default', 'default', 7, '', 1, NOW(), 0, 1, 'themes/default', 1, '', '', '', '');

INSERT INTO `mail_templates` (`event`, `active`, `content_type`, `mail_subject`, `mail_body`, `mail_from_name`, `mail_from_email`, `mail_to`) VALUES
('USER_RECOVER', 1, 'text/plain', "{{ _('Ваш пароль на сайт') }} {{server.name}}", "{{ _('Ваш новый пароль: ') }} {{password}}", '', 'no-reply@cetera.ru', '{{user.email}}');