	function get_posts() {
		global $wpdb;

		//set_magic_quotes_runtime(0);
		$datalines = file($this->file); // Read the file into an array
		$importdata = implode('', $datalines); // squish it
		$importdata = str_replace(array ("\r\n", "\r"), "\n", $importdata);

		preg_match_all('|<item>(.*?)</item>|is', $importdata, $this->posts);
		$this->posts = $this->posts[1];
		$index = 0;
		foreach ($this->posts as $post) {
			preg_match('|<title>(.*?)</title>|is', $post, $post_title);
			$post_title = str_replace(array('<![CDATA[', ']]>'), '', $wpdb->escape( trim($post_title[1]) ));

			preg_match('|<link>(.*?)</link>|is', $post, $post_link);

			$import_id = explode('view.php?aid=', $post_link[0])[1];

			preg_match('|<pubdate>(.*?)</pubdate>|is', $post, $post_date_gmt);

			if ($post_date_gmt) {
				$post_date_gmt = strtotime($post_date_gmt[1]);
			} else {
				// if we don't already have something from pubDate
				preg_match('|<dc:date>(.*?)</dc:date>|is', $post, $post_date_gmt);
				$post_date_gmt = preg_replace('|([-+])([0-9]+):([0-9]+)$|', '\1\2\3', $post_date_gmt[1]);
				$post_date_gmt = str_replace('T', ' ', $post_date_gmt);
				$post_date_gmt = strtotime($post_date_gmt);
			}

			$post_date_gmt = gmdate('Y-m-d H:i:s', $post_date_gmt);
			$post_date = get_date_from_gmt( $post_date_gmt );

			preg_match_all('|<category>(.*?)</category>|is', $post, $categories);
			$categories = $categories[1];

			if (!$categories) {
				preg_match_all('|<dc:subject>(.*?)</dc:subject>|is', $post, $categories);
				$categories = $categories[1];
			}

			$cat_index = 0;
			foreach ($categories as $category) {
				$categories[$cat_index] = $wpdb->escape( html_entity_decode( $category ) );
				$cat_index++;
			}

			preg_match('|<guid.*?>(.*?)</guid>|is', $post, $guid);
			if ($guid)
				$guid = $wpdb->escape(trim($guid[1]));
			else
				$guid = '';

			preg_match('|<content:encoded>(.*?)</content:encoded>|is', $post, $post_content);
			$post_content = str_replace(array ('<![CDATA[', ']]>'), '', $wpdb->escape(trim($post_content[1])));

			if (!$post_content) {
				// This is for feeds that put content in description
				preg_match('|<description>(.*?)</description>|is', $post, $post_content);
				$post_content = $wpdb->escape( html_entity_decode( trim( $post_content[1] ) ) );
			}

			// Clean up content
			$post_content = preg_replace_callback('|<(/?[A-Z]+)|', array( &$this, '_normalize_tag' ), $post_content);
			$post_content = str_replace('<br>', '<br />', $post_content);
			$post_content = str_replace('<hr>', '<hr />', $post_content);

			$post_author = 1;
			$post_status = 'publish';
			$this->posts[$index] = compact('post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_status', 'guid', 'categories', 'import_id');
			$index++;
		}
	}
