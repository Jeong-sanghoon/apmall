<?
	session_start();
	session_destroy();
	session_regenerate_id();
?>
<script>
	location.replace("/_admin");
</script>