<table class="sbl_basicTable" style="margin-top: 20px;">
  <tr>
    <th>Query</th>
    <th>Time</th>
  </tr>
  <?php foreach ($rows as $row) : ?>
  <tr>
    <td>
      <em style="font-weight: bold;">SQL: </em> <?php echo $row["sql"] ?>
      <?php if ($row["binds"] !== "") : ?>
        <br/>
        <em style="font-weight: bold;">BIND VALUES: </em> <?php echo $row["binds"] ?>
      <?php endif ?>
    </td>
    <td style="text-align: center;">
      <?php echo $row["time"] ?> msec
    </td>
  </tr>
  <?php endforeach ?>
</table>
