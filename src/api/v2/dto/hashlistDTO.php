<?php

namespace APIv2;

abstract class BaseHashlist {
  /**
   * @var {@type string}
   */
  public $name;
  /**
   * @var {@type int} {@min 0}
   */
  public $format;
  /**
   * @var {@type int} {@min 0}
   */
  public $hashtypeId;
  /**
   * @var {@type string} {@default :}
   */
  public $saltSeparator;
  /**
   * @var {@type bool} {@default false}
   */
  public $isSecret;
  /**
   * @var {@type bool} {@default false}
   */
  public $isHexSalted;
  /**
   * @var {@type bool} {@default false}
   */
  public $isSalted;
  /**
   * @var {@type int} {@min 1} {@default 1}
   */
  public $accessGroupId;
  /**
   * @var {@type bool} {@default false}
   */
  public $useBrain;
  /**
   * @var {@type int}
   */
  public $brainFeatures;
}

class CreateHashlist extends BaseHashlist {
  /**
   * @var {@type string} {@choice paste,import,url,upload} {@default paste}
   */
  public $dataSourceType;
  /**
   * @var {@type string} {@required false}
   * 
   * It is not required when dataSourceType is 'upload', use multipart/form-data instead
   */
  public $dataSource;
}

class GetHashlist extends BaseHashlist {
  /**
   * @var {@type int} {@min 0}
   */
  public $id;
  /**
   * @var {@type int} {@min 0}
   */
  public $hashCount;
  /**
   * @var {@type int} {@min 0}
   */
  public $crackedCount;
  /**
   * @var {@type string}
   */
  public $notes;
}

// TODO: JsonPatch, custom PATCH format, or use PUT? currently, a null property means 'do not update'
//       which in most cases is probably fine since null seems not sensible
class UpdateHashlist {
  /**
   * @var {@type string} {@required false}
   */
  public $name;
  /**
   * @var {@type int} {@min 1} {@default 1} {@required false}
   */
  public $accessGroupId;
  /**
   * @var {@type string} {@required false}
   */
  public $notes;
}