<?php

return [

	'admin_center_index' => [
		function () {
			return 'system/admin-center/system';
		},
		function () {
			return 'system/admin-cache/summary';
		},
		function () {
			return 'system/admin-log/last';
		},
		function () {
			return 'system/admin-sitemap/sitemap';
		},
	],

	'get_searchable_types' => [
		function () {
			return 'page';
		},
	],

	'admin_bar_links' => [
		function () {
			if (\Orange\Portal\Core\App\Portal::getInstance()->content->id) {
				return [OP_WWW . '/admin/pages/edit/' . \Orange\Portal\Core\App\Portal::getInstance()->content->id, \Orange\Portal\Core\App\Lang::t('EDIT_PAGE')];
			} else {
				return null;
			}
		},
	],

	'build_sitemaps' => [
		function () {
			$sitemaps = [];
			if ($types = \Orange\Portal\Core\Model\ContentType::getTypesForSitemap()) {
				$custom = \Orange\Portal\Core\Model\ContentField::getRef('seo_sitemap_priority');
				foreach ($types as $sitemap_name => $priority) {
					$list = \Orange\Portal\Core\Model\Content::getList(array(
						'types' => array($sitemap_name),
						'access_user' => new \Orange\Portal\Core\Model\User(),
						'fields' => [
							'seo_hidden' => '1',
							'seo_sitemap_priority' => '-1',
						],
						'fields_not' => true,
					), '\Orange\Portal\Core\Model\Content');
					$lTime = 0;
					$indexFile = new \Orange\FS\File('sites/' . \Orange\Portal\Core\App\Portal::$sitecode . '/static/root', 'sitemap_' . $sitemap_name . '.xml');
					if ($list) {
						$sitemap = new \Orange\Sitemap\Urlset();
						$count = 0;
						foreach ($list as $item) {
							if (!isset($custom[$item->id])) {
								$custom[$item->id] = 0;
							}
							if (($custom[$item->id] > 0) || (($priority > 0) && ($custom[$item->id] >= 0))) {
								$iPriority = $custom[$item->id] > 0 ? $custom[$item->id] : $priority;
								//TODO Add support of "Change frequency"
								$sitemap->addUrl(OP_WWW . '/' . $item->getSlug(\Orange\Portal\Core\App\Portal::config('system_default_lang')), $cTime = $item->get('content_time_modified'), null, $iPriority / 100);
								$count++;
								if (strtotime($cTime) > $lTime) {
									$lTime = $cTime;
								}
							}
						}
						if ($count) {
							$indexFile->save($sitemap->build());
							$sitemaps[$sitemap_name] = $lTime;
						} else {
							if ($indexFile->exists()) {
								$indexFile->remove();
							}
						}
					} else {
						if ($indexFile->exists()) {
							$indexFile->remove();
						}
					}

				}
			}
			return $sitemaps;
		},
	],

];