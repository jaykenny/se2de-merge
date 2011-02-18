<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/lib2/error.inc.php';

class ChildWp_Presenter
{
  const req_cache_id = 'cacheid';
  const req_child_id = 'childid';
  const req_delete_id = 'deleteid';
  const req_wp_type = 'wp_type';
  const req_wp_desc = 'desc';
  const tpl_page_title = 'pagetitle';
  const tpl_cache_id = 'cacheid';
  const tpl_child_id = 'childid';
  const tpl_delete_id = 'deleteid';
  const tpl_wp_type = 'wpType';
  const tpl_wp_desc = 'wpDesc';
  const tpl_wp_type_ids = 'wpTypeIds';
  const tpl_wp_type_names = 'wpTypeNames';
  const tpl_wp_type_error = 'wpTypeError';
  const tpl_submit_button = 'submitButton';
  const tpl_disabled = 'disabled';

  private $request;
  private $translator;
  private $coordinate;
  private $waypointTypes = array();
  private $waypointTypeValid = true;
  private $type = '0';
  private $description;
  private $cacheId;
  private $childId;
  private $childWpHandler;
  private $isDelete;

  public function __construct($request = false, $translator = false)
  {
    $this->request = $this->initRequest($request);
    $this->translator = $this->initTranslator($translator);
    $this->coordinate = new Coordinate_Presenter($this->request, $this->translator);
  }

  private function initRequest($request)
  {
    if ($request)
      return $request;

    return new Http_Request();
  }

  private function initTranslator($translator)
  {
    if ($translator)
      return $translator;

    return new Language_Translator();
  }

  public function doSubmit()
  {
    $coordinate = $this->coordinate->getCoordinate();
    $description = htmlspecialchars($this->getDesc(), ENT_COMPAT, 'UTF-8');

    if ($this->childId)
    {
      if ($this->isDelete)
        $this->childWpHandler->delete($this->childId);
      else
        $this->childWpHandler->update($this->childId, $this->getType(), $coordinate->latitude(), $coordinate->longitude(), $description);
    }
    else
      $this->childWpHandler->add($this->cacheId, $this->getType(), $coordinate->latitude(), $coordinate->longitude(), $description);
  }

  private function getType()
  {
    return $this->request->get(self::req_wp_type, $this->type);
  }

  private function getDesc()
  {
    return $this->request->get(self::req_wp_desc, $this->description);
  }

  public function init($template, $cacheManager, $childWpHandler)
  {
    $this->childWpHandler = $childWpHandler;

    $this->cacheId = $this->request->getForValidation(self::req_cache_id);

    if (!$cacheManager->exists($this->cacheId) || !$cacheManager->userMayModify($this->cacheId))
      $template->error(ERROR_CACHE_NOT_EXISTS);

    $this->setTypes($childWpHandler->getChildWpTypes());
    $this->childId = $this->request->getForValidation(self::req_child_id);

    if (!$this->childId)
    {
      $this->childId = $this->request->getForValidation(self::req_delete_id);

      if ($this->childId)
        $this->isDelete = true;
    }

    if ($this->childId)
    {
      $childWp = $this->childWpHandler->getChildWp($this->childId);

      if (empty($childWp) || $this->cacheId != $childWp['cacheid'])
        $template->error(ERROR_CACHE_NOT_EXISTS);

      $this->type = $childWp['type'];
      $this->description = htmlspecialchars_decode($childWp['description']);
      $this->coordinate->init($childWp['latitude'], $childWp['longitude']);
    }
  }

  public function prepare($template)
  {
    $template->assign(self::tpl_page_title, $this->translator->Translate($this->getTitle()));
    $template->assign(self::tpl_submit_button, $this->translator->Translate($this->getSubmitButton()));
    $template->assign(self::tpl_cache_id, $this->cacheId);
    $template->assign(self::tpl_wp_desc, $this->getDesc());
    $template->assign(self::tpl_wp_type, $this->getType());
    $template->assign(self::tpl_disabled, $this->isDelete);
    $this->prepareTypes($template);
    $this->coordinate->prepare($template);

    if ($this->isDelete)
      $template->assign(self::tpl_delete_id, $this->childId);
    else
      $template->assign(self::tpl_child_id, $this->childId);

    if (!$this->waypointTypeValid)
      $template->assign(self::tpl_wp_type_error, $this->translator->translate('Select waypoint type'));
  }

  private function prepareTypes($template)
  {
    $template->assign(self::tpl_wp_type_ids, $this->getWaypointTypeIds());
    $template->assign(self::tpl_wp_type_names, $this->waypointTypes);
  }

  private function getTitle()
  {
    if ($this->childId)
    {
      if ($this->isDelete)
        return 'Delete waypoint';

      return 'Edit waypoint';
    }

    return 'Add waypoint';
  }

  private function getSubmitButton()
  {
    if ($this->childId)
    {
      if ($this->isDelete)
        return 'Delete';

      return 'Save';
    }

    return 'Add new';
  }

  private function getWaypointTypeIds()
  {
    return array_keys($this->waypointTypes);
  }

  private function setTypes($waypointTypes)
  {
    $this->waypointTypes = array();

    foreach ($waypointTypes as $type)
    {
      $this->waypointTypes[$type->getId()] = $this->translator->translate($type->getName());
    }
  }

  public function validate()
  {
    if ($this->isDelete)
      return true;

    $wpTypeValidator = new Validator_Array($this->getWaypointTypeIds());

    $this->request->validate(self::req_wp_desc, new Validator_AlwaysValid());
    $this->waypointTypeValid = $this->request->validate(self::req_wp_type, $wpTypeValidator);
    $coordinateValid = $this->coordinate->validate();

    return $this->waypointTypeValid && $coordinateValid;
  }

  public function getCacheId()
  {
    return $this->cacheId;
  }
}

?>
