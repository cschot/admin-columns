<?php

namespace AC\Admin\Promo;

use AC\Admin\Promo;

class BlackFriday extends Promo {

	public function __construct() {

		$this->set_title( '30% Off from Black Friday until Cyber Monday' );
		$this->set_discount( 30 );

		$this->add_date_range( '2016-11-25', '2016-11-29' );
		$this->add_date_range( '2017-11-24', '2017-11-28' );
		$this->add_date_range( '2018-11-23', '2018-11-27' );
		$this->add_date_range( '2019-11-29', '2019-12-3' );
		$this->add_date_range( '2020-11-27', '2020-11-31' );
	}

}