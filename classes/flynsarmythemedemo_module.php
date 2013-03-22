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




			/**
			 * Below this point shouldn't be necessary but is until
			 * http://forum.lemonstandapp.com/tracker/issue-494-cmsongetactivetheme-event-should-ignore-non-object-responses/
			 * is fixed.
			 */





			$themes = Db_DbHelper::objectArray('select id, agent_detection_mode, agent_list, agent_detection_code, name, code from cms_themes where is_enabled is not null and is_enabled=1 order by name');

			/*
			 * Try to select a theme based on the user agent
			 */

			$agent = Phpr::$request->getUserAgent();
			$known_agents = self::get_agent_list();

			foreach ($themes as $theme)
			{
				if (!$theme->agent_detection_mode || $theme->agent_detection_mode == Cms_Theme::agent_detection_disabled)
					continue;

				if ($theme->agent_detection_mode == Cms_Theme::agent_detection_built_in)
				{
					$theme_agents = Cms_Theme::decode_agent_list($theme->agent_list);
					foreach ($theme_agents as $theme_agent_id)
					{
						foreach ($known_agents as $agent_id=>$agent_info)
						{
							if ($agent_id == $theme_agent_id && strpos($agent, $agent_info['signature']) !== false)
								return Cms_Theme::create()->where('id=?', $theme->id)->find();
						}
					}
				}

				if (strlen($theme->agent_detection_code))
				{
					try
					{
						if (@eval($theme->agent_detection_code))
							return Cms_Theme::create()->where('id=?', $theme->id)->find();
					} catch (exception $ex)
					{
						throw new Phpr_SystemException(
							sprintf('Error evaluating the user agent detection code for theme "%s (%s)". %s',
								$theme->name,
								$theme->code,
								Core_String::finalize($ex->getMessage())
							)
						);
					}
				}
			}

			/*
			 * Try to return a default theme
			 */

			$theme = Cms_Theme::get_default_theme();
			if ($theme)
				return $theme;

			/*
			 * Return the first theme in the list
			 */

			if (count($themes))
				return Cms_Theme::create()->where('id=?', $themes[0]->id)->find();

			return null;
		}

		/**
		 * This method shouldn't be necessary but is until
		 * http://forum.lemonstandapp.com/tracker/issue-494-cmsongetactivetheme-event-should-ignore-non-object-responses/
		 * is fixed.
		 */
		protected static function get_agent_list()
		{
			return array(
				'blackberry'=>array('name'=>'BlackBerry', 'signature'=>'BlackBerry'),
				'android'=>array('name'=>'Android', 'signature'=>'Android'),
				'ipad'=>array('name'=>'Apple iPad', 'signature'=>'iPad'),
				'iphone'=>array('name'=>'Apple iPhone', 'signature'=>'iPhone'),
				'ipod'=>array('name'=>'Apple iPod Touch', 'signature'=>'iPod'),
				'google'=>array('name'=>'Googlebot', 'signature'=>'Googlebot'),
				'msnbot'=>array('name'=>'Msnbot', 'signature'=>'msnbot'),
				'yahoo'=>array('name'=>'Yahoo! Slurp', 'signature'=>'Yahoo! Slurp'),
			);
		}
	}
