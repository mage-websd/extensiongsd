<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    My
 * @package     My_Igallery
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Image block
 *
 * @category   My
 * @package    My_Igallery
 * @author     Theodore Doan <theodore.doan@gmail.com>
 */
class My_Igallery_Block_Adminhtml_Banner_Edit_Tab_Image extends Mage_Adminhtml_Block_Widget {

    protected function _prepareForm() {
        $data = $this->getRequest()->getPost();
        $form = new Varien_Data_Form();
        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function __construct() {
        parent::__construct();
        $this->setTemplate("my_igallery/edit/tab/image.phtml");
        $this->setId("media_gallery_content");
        $this->setHtmlId("media_gallery_content");
    }

    protected function _prepareLayout() {
        $this->setChild("uploader", $this->getLayout()->createBlock("igallery/adminhtml_media_uploader"));
        $this->getUploader()->getConfig()->setUrl(Mage::getModel("adminhtml/url")->addSessionParam()->getUrl("*/*/image"))->setFileField("image")->setFilters(array("images" => array("label" => Mage::helper("adminhtml")->__("Images (.gif, .jpg, .png)"), "files" => array("*.gif", "*.jpg", "*.jpeg", "*.png"))));
        $this->setChild("delete_button", $this->getLayout()->createBlock("adminhtml/widget_button")->addData(array("id" => "{{id}}-delete", "class" => "delete", "type" => "button", "label" => Mage::helper("adminhtml")->__("Remove"), "onclick" => $this->getJsObjectName() . ".removeFile('{{fileId}}')")));
        return parent::_prepareLayout();
    }

    public function getUploader() {
        return $this->getChild("uploader");
    }

    public function getUploaderHtml() {
        return $this->getChildHtml("uploader");
    }

    public function getJsObjectName() {
        return $this->getHtmlId() . "JsObject";
    }

    public function getAddImagesButton() {
        return $this->getButtonHtml(Mage::helper("catalog")->__("Add New Images"), $this->getJsObjectName() . ".showUploader()", "add", $this->getHtmlId() . "_add_images_button");
    }

    public function getImagesJson() {
        $_model = Mage::registry("banner_data");
        $_data = $_model->getImage();
        if (is_array($_data) and sizeof($_data) > 0) {
            $_result = array();
            foreach ($_data as &$_item) {
                $_result[] = array("value_id" => $_item["image_id"], "url" => Mage::getSingleton("igallery/config")->getBaseMediaUrl() . $_item["file"], "file" => $_item["file"], "label" => $_item["label"], "position" => $_item["position"], "disabled" => $_item["disabled"]);
            }return Zend_Json::encode($_result);
        }return "[]";
    }

    public function getImagesValuesJson() {
        $values = array();
        return Zend_Json::encode($values);
    }

    public function getMediaAttributes() {
        
    }

    public function getImageTypes() {
        $type = array();
        $type["gallery"]["label"] = "igallery";
        $type["gallery"]["field"] = "igallery";
        $imageTypes = array();
        return $type;
    }

    public function getImageTypesJson() {
        return Zend_Json::encode($this->getImageTypes());
    }

    public function getCustomRemove() {
        return $this->setChild("delete_button", $this->getLayout()->createBlock("adminhtml/widget_button")->addData(array("id" => "{{id}}-delete", "class" => "delete", "type" => "button", "label" => Mage::helper("adminhtml")->__("Remove"), "onclick" => $this->getJsObjectName() . ".removeFile('{{fileId}}')")));
    }

    public function getDeleteButtonHtml() {
        return $this->getChildHtml("delete_button");
    }

    public function getCustomValueId() {
        return $this->setChild("value_id", $this->getLayout()->createBlock("adminhtml/widget_button")->addData(array("id" => "{{id}}-value", "class" => "value_id", "type" => "text", "label" => Mage::helper("adminhtml")->__("ValueId"),)));
    }

    public function getValueIdHtml() {
        return $this->getChildHtml("value_id");
    }

}