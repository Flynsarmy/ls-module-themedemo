<?

	class FlynsarmyThemeDemo_Module extends Core_ModuleBase {
		protected function createModuleInfo() {
			return new Core_ModuleInfo(
				"Theme Demo",
				"Lets visitors try demo LS themes",
				"Flynsarmy"
			);
		}

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
