<?php

class Rss_LogsFeedData extends Rss_FeedData
{
  private $translator;
  private $items;

  public function __construct($translator, $items, $title, $atomLink)
  {
    parent::__construct($title, $translator->translate('New cache logs'), $atomLink);

    $this->translator = $translator;
    $this->items = $items;
  }

  public function getItems()
  {
    return $this->items->getItems();
  }

  public function getItemTitle($r)
  {
    $r['rss_log_type'] = $this->getLogType($r['log_type']);

    return $this->translator->translateArgs('{rss_username} {rss_log_type} {rss_cache_name}', $r);
  }

  public function getItemDescription($r)
  {
    return $r['log_text'];
  }

  public function getItemLink($r)
  {
    return 'http://www.opencaching.de/' . 'viewcache.php?cacheid='.$r['cache_id'];
  }

  public function getItemDate($r)
  {
    return strtotime($r['log_date']);
  }

  public function getItemId($r)
  {
    return $r['log_id'];
  }

  public function getItemEnclosure($r)
  {
    return '<enclosure url="' . 'http://www.opencaching.de/' . 'rss/'.$r['cache_id'].'.gpx" length="4096" type="application/gpx" />';
  }

  function getLogType($log_type)
  {
    switch($log_type)
    {
      case Log_Type::Found:
        return $this->translator->translate('found');

      case Log_Type::NotFound:
        return $this->translator->translate('did not find');

      case Log_Type::Comment:
        return $this->translator->translate('wrote a comment on');

      case Log_Type::Attended:
        return $this->translator->translate('attended');

      case Log_Type::WillAttend:
        return $this->translator->translate('will attend');

      case Log_Type::Solved:
        return $this->translator->translate('solved');

      case Log_Type::NotSolved:
        return $this->translator->translate('did not solve');
    }

    return $this->translator->translate('log_type_unknown') . $log_type;
  }
}

?>