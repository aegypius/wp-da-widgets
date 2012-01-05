	<p>
		<label for="<?php echo $this->get_field_id('title')?>"><?php _e('Title', 'da-widgets')?> : </label>
		<input class="widefat" type="text" id="<?php echo $this->get_field_id('title')?>" name="<?php echo $this->get_field_name('title')?>" value="<?php echo $title?>" />
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('type')?>"><?php _e('Content', 'da-widgets')?> : </label>
		<select class="widefat" id="<?php echo $this->get_field_id('type')?>" name="<?php echo $this->get_field_name('type')?>">
			<option <?php selected(self::DA_WIDGET_LOG, $type); ?> value="<?php echo self::DA_WIDGET_LOG?>"><?php _e('Journal', 'da-widgets')?></option>
			<option <?php selected(self::DA_WIDGET_GALLERY, $type); ?> value="<?php echo self::DA_WIDGET_GALLERY?>"><?php _e('Gallery', 'da-widgets')?></option>
			<option <?php selected(self::DA_WIDGET_FAVOURITE, $type); ?> value="<?php echo self::DA_WIDGET_FAVOURITE?>"><?php _e('Favourites', 'da-widgets')?></option>
		</select>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('deviant')?>"><?php _e('Deviant', 'da-widgets')?> : </label>
		<input class="widefat" type="text" id="<?php echo $this->get_field_id('deviant')?>" name="<?php echo $this->get_field_name('deviant')?>" value="<?php echo $deviant?>" />
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('items')?>"><?php _e('Items to display', 'da-widgets')?> : </label>
		<select class="widefat" id="<?php echo $this->get_field_id('items')?>" name="<?php echo $this->get_field_name('items')?>">

			<option <?php selected(-1 , $items) ?> value="-1"><?php _e('All', 'da-widgets')?></option>

		<?php foreach (range(1,10) as $v) : ?>
			<option <?php selected($v , $items) ?> value="<?php echo $v?>"><?php echo $v?></option>
		<?php endforeach; ?>
		</select>
	</p>

	<?php if ($type == self::DA_WIDGET_LOG) : ?>
	<p>
		<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('html')?>" name="<?php echo $this->get_field_name('html')?>" value="1" <?php if ( $html ) { echo 'checked="checked"'; } ?>/>
		<label for="<?php echo $this->get_field_id('html')?>"><?php _e('Keep original formating', 'da-widgets')?></label>
	</p>
	<?php else : ?>
	<p>
		<label for="<?php echo $this->get_field_id('rating')?>"><?php _e('Content Rating', 'da-widgets')?> : </label>
		<select class="widefat" id="<?php echo $this->get_field_id('rating')?>" name="<?php echo $this->get_field_name('rating')?>">
			<option <?php selected('nonadult', $rating); ?> value="nonadult"><?php _e('Forbid adult content', 'da-widgets')?></option>
			<option <?php selected('all', $rating); ?> value="all"><?php _e('Allow adult content', 'da-widgets')?></option>
		</select>
	</p>
<?php
		switch ($type) {
			case self::DA_WIDGET_GALLERY:
				$res = new DeviantArt_Gallery($deviant, $rating, $scraps);
				break;
			default:
			case self::DA_WIDGET_FAVOURITE:
				$res = new DeviantArt_Favourite($deviant, $rating, $scraps);
				break;
		}

		foreach ($res->getCategories() as $categoryId=>$categoryName) {
			$options .= sprintf('<option value="%s"%s>%s</option>', $categoryId, ($categoryId == $filter ? ' selected="selected"' : ''), $categoryName);
		}
?>
	<p>
		<label for="<?php echo $this->get_field_id('filter')?>"><?php _e('Category filter', 'da-widgets')?> : </label>
		<select class="widefat" id="<?php echo $this->get_field_id('filter')?>" name="<?php echo $this->get_field_name('filter')?>">
			<option <?php selected(0, $filter); ?> value="0"><?php _e('Disabled', 'da-widgets')?></option>
			<?php echo $options ?>
			<option <?php selected(-1, $filter); ?> value="-1"><?php _e('Scraps', 'da-widgets')?></option>
		</select>
	</p>

	<?php endif; ?>
