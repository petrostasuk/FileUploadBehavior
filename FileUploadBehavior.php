<?php
/**
 * Author : petro.stasuk.990@gmail.com
 * Date: 27.03.14
 * Time: 22:20
 */

class FileUploadBehavior extends CActiveRecordBehavior
{
    public $fileAlias = 'webroot.upload';

    public $attribute = 'file';

    public $types = 'jpg, jpeg, png';

    public function beforeSave($event)
    {
        $file = CUploadedFile::getInstance($this->owner, $this->attribute);
        if ($file) {
            $tempName = uniqid().'.'.$file->getExtensionName();
            $dir = Yii::getPathOfAlias($this->fileAlias).DIRECTORY_SEPARATOR;
            $file->saveAs($dir.$tempName);
            $this->owner->setAttribute($this->attribute, $tempName);
        } else
            $this->owner->setAttribute($this->attribute, $this->_old_file);
        $event->isValid = true;
    }

    protected $_old_file;

    public function afterFind($event)
    {
        $this->_old_file = $this->owner->{$this->attribute};
    }

    public function afterDelete($event)
    {
        $file = $this->owner->{$this->attribute};
        $fullFileName = Yii::getPathOfAlias($this->fileAlias).DIRECTORY_SEPARATOR.$file;
        if ($file && file_exists($fullFileName))
            @unlink($fullFileName);
    }

    public function attach($owner) {
        parent::attach($owner);
        $validators = $this->owner->getValidatorList();
        $validator = CValidator::createValidator('file', $this->owner, $this->attribute, array('types'=>$this->types, 'allowEmpty'=>true));
        $validators->add($validator);
    }

    public function getFileUrl()
    {
        $filePath = Yii::getPathOfAlias($this->fileAlias);
        $webrootPath = Yii::getPathOfAlias('webroot');
        if(strpos($filePath, $webrootPath) !== FALSE) {
            $generatedPath = substr($filePath, strlen($webrootPath));
        }
        $generatedUrl = str_replace('\\', '/', Yii::app()->urlManager->getBaseUrl().$generatedPath.DIRECTORY_SEPARATOR.$this->owner->{$this->attribute});
        return $generatedUrl;
    }

} 