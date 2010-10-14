<?php
class ComAlchemyViewDashboardHtml extends ComDefaultViewHtml
{
	public function display()
	{
		//Reset the toolbar
		KFactory::get('admin::com.alchemy.toolbar.dashboard')
			->reset();

		return parent::display();
	}
}