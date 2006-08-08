<div style="border: 1px dotted; background-color: #EFE;">
  <h3>記事番号<? echo $id ?>番</h3>
    タイトル: <? echo $title ?> <br />
    本文: <? echo $body ?>

    <? if ($id == 15) : ?>
      <p style="color: red;">あたり</p>
    <? endif; ?>
</div>

