<?php
// Create the controller dispatcher
echo KFactory::get('admin::com.alchemy.dispatcher')->dispatch(KRequest::get('get.view', 'cmd', 'dashboard'));
