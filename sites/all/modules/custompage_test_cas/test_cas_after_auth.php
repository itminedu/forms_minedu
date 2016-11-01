<?php
drupal_set_message ("hellooooooooooo from test_cas_after_auth.php ");
?>
Authentication succeeded for user
<strong><?php echo phpCAS::getUser(); ?></strong>.
<h3>User Attributes</h3>
<ul>
<?php
foreach (phpCAS::getAttributes() as $key => $value) {
if (is_array($value)) {
echo '<li>', $key, ':<ol>';
foreach ($value as $item) {
echo '<li><strong>', $item, '</strong></li>';
drupal_set_message ($item);
}
echo '</ol></li>';
} else {
echo '<li>', $key, ': <strong>', $value, '</strong></li>' . PHP_EOL;
}
}
?>
</ul>
