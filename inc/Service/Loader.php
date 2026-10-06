<?php

namespace GHOSTLABS\UNDERCARD\Service;

class Loader {

    protected array $actions;

    protected array $filters;

	public function __construct() {

		$this->actions = [];
		$this->filters = [];
	}
	
	public function add_action( string $hook, object $component, string $callback, int $priority = 10, int $accepted_args = 1 ) : void {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}
	
	public function add_filter( string $hook, object $component, string $callback, int $priority = 10, int $accepted_args = 1 ) : void {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	private function add( array $hooks, string $hook, object $component, string $callback, int $priority, int $accepted_args ) : array {

		$hooks[] = [
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		];

		return $hooks;

	}
	
	public function run() : void {

		foreach ( $this->filters as $hook ) {
			$callback = [ $hook['component'], $hook['callback'] ];

			if ( ! is_callable( $callback ) ) {
				continue;
			}

			add_filter( $hook['hook'], $callback, $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			$callback = [ $hook['component'], $hook['callback'] ];

			if ( ! is_callable( $callback ) ) {
				continue;
			}

			add_action( $hook['hook'], $callback, $hook['priority'], $hook['accepted_args'] );
		}

	}
}