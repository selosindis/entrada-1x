var grow;

function growPic(official_photo, uploaded_photo, official_link, uploaded_link, zoomout) {
	if (!grow) {

		$$('.zoomin').each(function (e) { e.innerHTML = ''; });
		
		if (official_photo) {
			new Effect.Scale(official_photo, 300, 
			{
				scaleMode: 
				{ 
					originalHeight:	100, 
					originalWidth:	72
				},
				beforeStart: function() {
					official_photo.style.zIndex = 8;
				},
				afterFinish: function() {
					zoomout.innerHTML = '-';
					
					grow = true;
				}
			});
			
			if (official_link) {
				official_link.style.zIndex = 10;
				new Effect.Morph(official_link, 
				{
					style: 'left: 15px; bottom: -185px; font-size: 24px; line-height: 26px; padding: 0px 5px 0px 5px;',
					duration: 1.0
				});
			}
		}

		if (uploaded_photo) {
			new Effect.Scale(uploaded_photo, 300, 
			{
				scaleMode: 
				{ 
					originalHeight:	100, 
					originalWidth:	72 
				},
				beforeStart: function() {
					uploaded_photo.style.zIndex	= 7;
				},
				afterFinish: function() {
					zoomout.innerHTML = '-';
					
					grow = true;
				}
			});
			
			if (uploaded_link) {
				uploaded_link.style.zIndex = 10;
				new Effect.Morph(uploaded_link, 
				{
					style: 'left: 47px; bottom: -185px; font-size: 24px; line-height: 26px; padding: 0px 5px 0px 5px;',
					duration: 1.0
				});
			}
		}
	}

	return false;
}

function shrinkPic(official_photo, uploaded_photo, official_link, uploaded_link, zoomout) {
	if ((official_photo && official_photo.width > 72) || (uploaded_photo && uploaded_photo.width > 72)) {
		
		zoomout.innerHTML = '';
		
		if (official_photo) {
			new Effect.Scale(official_photo, 100, 
			{
				scaleFrom: (official_photo.width / 72 * 100), 
				scaleMode: 
				{ 
					originalHeight:	100, 
					originalWidth:	72 
				},
				afterFinish: function() {
					$$('.zoomin').each(function (e) { e.innerHTML = '+'; });
					
					official_photo.style.zIndex = 6;
					
					grow = false;
				}
			});
			
			if (official_link) {
				new Effect.Morph(official_link, 
				{
					style: 'left: 5px; bottom: 5px; font-size: 9px; line-height: 10px;  padding: 0px 2px 0px 2px;',
					duration: 1.0
				});
				official_link.style.zIndex = 6;
			}
		}
		
		if (uploaded_photo) {
			new Effect.Scale(uploaded_photo, 100, 
			{
				scaleFrom: (uploaded_photo.width / 72 * 100), 
				scaleMode: 
				{ 
					originalHeight: 100, 
					originalWidth: 72 
				},
				afterFinish: function() {
					$$('.zoomin').each(function (e) { e.innerHTML = '+'; });
					
					uploaded_photo.style.zIndex = 5;
					
					grow = false;
				}
			});
			
			if (uploaded_link) {
				new Effect.Morph(uploaded_link, 
				{
					style: 'left: 19px; bottom: 5px; font-size: 9px; line-height: 10px; padding: 0px 2px 0px 2px;',
					duration: 1.0
				});
				uploaded_link.style.zIndex = 6;
			}
		}
	}

	return false;
}

var transitionRunning = false;

function hideOfficial(official_photo, active, inactive) {
	if (!transitionRunning) {
		transitionRunning = true;
		new Effect.Fade(official_photo,
		{
			duration: 0.3,
//			from: 1.0,
			to: 0.0,
			afterFinish: function() {
//				inactive.className	= 'not-selected';
//				active.className	= 'selected';
				transitionRunning	= false;
			}
		});
	}
}

function showOfficial(official_photo, active, inactive) {
	if (!transitionRunning) {
		transitionRunning = true;
		new Effect.Appear(official_photo,
		{
			duration: 0.3,
//			from: 0.0,
			to: 1.0,
			afterFinish: function() {
//				inactive.className	= 'not-selected';
//				active.className	= 'selected';
				transitionRunning	= false;
			}
		});
	}
}