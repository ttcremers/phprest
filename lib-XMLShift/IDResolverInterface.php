<?php
interface IDResolverInterface {
	// Should return Object found with id
	function resolve($id, $className);
}
?>