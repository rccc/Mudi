<?php

namespace Mudi\Collection;

/**
 * Cette classe contient les données échangées sous le formes de paires clé/valeur 
 * entre commandes/contrôleurs d'une part et services de l'autre
 * La valeur peut contenir tout type de données
 */

abstract class Collection implements \IteratorAggregate
{
	protected $items = array();

	public function __construct(array $items = array())
	{
		$this->items = $items;
	}

	public function add($key, $object)
	{
		$this->items[$key] = $object;
	}

	public function remove($key)
	{
		unset($this->items[$key]);
	}

	public function get($key, $default = null)
	{
		if(array_key_exists($key, $this->items))
		{
			return $this->items[$key];
		}

		return $default;
	}

	public function all()
	{
		return $this->items;
	}

	public function keys()
	{
		return array_keys($this->items);
	}

	public function exists($key)
	{
		return isset($this->items[$key]);
	}

	public function getIterator() {
		return new \ArrayIterator($this);
	}

}