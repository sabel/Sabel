<table class="querylog" style="border: solid 1px #bbb; border-collapse: collapse; margin-top: 20px;">
  <tr>
    <th style="width: 500px;">Query</th>
    <th style="width: 150px;">Time</th>
  </tr>
  <?php foreach ($rows as $row) : ?>
  <tr>
    <td style="border: solid 1px #bbb;">
      <em style="font-weight: bold;">SQL: </em> <?php echo $row["sql"] ?>
      <?php if ($row["binds"] !== "") : ?>
        <br/>
        <em style="font-weight: bold;">BIND VALUES: </em> <?php echo $row["binds"] ?>
      <?php endif ?>
    </td>
    <td style="border: solid 1px #bbb; text-align: center;">
      <?php echo $row["time"] ?> msec
    </td>
  </tr>
  <?php endforeach ?>
</table>
