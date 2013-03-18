<?

	class FlynsarmyThemeDemo_Module extends Core_ModuleBase {
		protected function createModuleInfo() {
			return new Core_ModuleInfo(
				"Theme Demo",
				"Lets visitors try demo LS themes",
				"Flynsarmy"
			);
		}



		/**
		 * Access points
		 */

		public function register_access_points()
		{
			return array(
				'flynsarmythemedemo_demo_theme'=>'demo_theme',
				'flynsarmythemedemo_restore_default_theme' => 'restore_default_theme',
			);
		}

		public function demo_theme( $params )
		{
			// Set the new theme
			if ( isset($params[0]) )
				setcookie('ls_theme', $params[0], time()+60*6*24*365, '/');

			$this->redirect_to_referrer();
		}

		public function restore_default_theme( $params )
		{
			setcookie('ls_theme', '', time()-10, '/');

			$this->redirect_to_referrer();
		}

		public function redirect_to_referrer()
		{
			// Redirect to referer if it exists, else redirect to index
			if ( isset($_SERVER['HTTP_REFERER']) )
				header("Location: " . $_SERVER["HTTP_REFERER"]);
			else
				header("Location: " . root_url('', true));
		}



		/**
		 * Events
		 */

		public function subscribeEvents() {
			Backend::$events->addEvent('cms:onGetActiveTheme', $this, 'get_active_theme');
		}

		public function get_active_theme()
		{
			if ( isset($_COOKIE['ls_theme']) )
			{
				$theme = Cms_Theme::create()->find_by_code( $_COOKIE['ls_theme'] );
				if ( $theme )
					return $theme;
			}

			return Cms_Theme::get_default_theme();
		}
	}
