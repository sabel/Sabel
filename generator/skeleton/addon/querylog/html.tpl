<table style="border: solid 1px #bbb; border-collapse: collapse;">
  <tr>
    <th style="width: 500px;">Query</th>
    <th style="width: 150px;">Time</th>
  </tr>
  <?php foreach ($buf as $row) : ?>
  <tr>
    <td style="border: solid 1px #bbb;"><?php echo $row["sql"] ?></td>
    <td style="border: solid 1px #bbb; text-align: center;"><?php echo $row["time"] ?> msec</td>
  </tr>
  <?php endforeach ?>
</table>
