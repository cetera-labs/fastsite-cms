<?php
namespace Cetera\User; 

interface UserInterface {
    public function allowBackOffice();
    public function allowAdmin();
    public function isSuperUser();
    public function allowCat($permission, $catalog);
    public function allowFilesystem($path);
    public function isEnabled();
    public function isInGroup($group_id);
}
