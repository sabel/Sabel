<?php

/**
 * this class has information of page
 *
 * @author Mori Reo <mori.reo@servise.jp>
 */
class SabelPager
{
  // page offset
  protected $pageOffset = 5;
  
  // number of items per page
  protected $pageItem = null;
  
  // all number of items
  protected $numberOfItems = null;
  
  // all number of page
  protected $numberOfPages = null;
  
  /**
   * initialize pager
   *
   * @param int $numberOfItems number of items per page.
   * @param int $numberOfPageItems
   */
  public function initialize($numberOfItems, $numberOfPageItems)
  {
    $this->setNumberOfItems($numberOfItems);
    $this->setPageItem($numberOfPageItems);
    return $this;
  }
  
  public function setNumberOfItems($numberOfItems)
  {
    if ($numberOfItems < 0 || is_null($numberOfItems)) {
      throw new Exception("invalid number of items: " . $numberOfItems);
    }
    
    $this->numberOfItems = $numberOfItems;
    $this->setNumberOfPages();
  }
  
  public function setPageItem($numberOfPageItems)
  {
    $this->pageItem = $numberOfPageItems;
    $this->setNumberOfPages();
  }
  
  public function getPageItem()
  {
    return $this->pageItem;
  }
  
  public function getPageOffset()
  {
    return $this->pageOffset;
  }
  
  public function getNumItems()
  {
    return $this->numberOfItems;
  }
  
  public function getNumberOfPage()
  {
    return $this->numberOfPages;
  }
  
  public function check()
  {
    if ($this->numberOfPages) {
      return true;
    } else {
      return false;
    }
  }
  
  /**
   * alias for getNumberOfPage() method.
   */
  public function getNumberOfPages()
  {
    return $this->getNumberOfPage();
  }
  
  public function getSqlOffset($pageNum)
  {
    return floor(($pageNum-1) * $this->pageItem);
  }
  
  /**
   * alias for getLastPageOffset() method.
   */
  public function getLastPageSqlOffset()
  {
    return $this->getLastPageOffset();
  }
  
  public function getLastPageOffset()
  {
    $offset = $this->getNumberOfPages() * $this->pageItem;
    
    // fixing when page is last page
    if ($offset == $this->numberOfItems) {
      $offset -= $this->pageOffset;
    }
    
    return $offset;
  }

  protected function setNumberOfPages()
  {
    if (isset($this->numberOfItems) && isset($this->pageItem)) {
      $this->numberOfPages =(int) ceil($this->numberOfItems / $this->pageItem);
    }
  }
}

?>
