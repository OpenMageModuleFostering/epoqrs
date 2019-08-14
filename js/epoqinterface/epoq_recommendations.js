function addRecommendationsToPage(recommendationPids, recommendationId, data, section)
{
		var recWidgetFromShopURL = epoq_baseUrl + "epoqinterface/recommendation/list?";  

		for(var i=0; i < recommendationPids.length; i++){
			recWidgetFromShopURL+='pid[]='+recommendationPids[i]+'&';
		}
		
		recWidgetFromShopURL+='section='+section+'&';

		//(be careful "same origin policy").
		jQuery.ajax({
			 url: recWidgetFromShopURL,
			 cache: false,
			 success: function(html){
				jQuery("#epoqWidget1").append(html);   //alter here for auto tracking clicks on recommendations
                jQuery("#epoqWidget1 a").each( function()
                {
                     if(this.href.indexOf("?") !== -1) {
                         this.href+="&";
                     } else {
                         this.href+="?";
                     }

                     this.href += "recommendation_id=" + recommendationId;
                });

			 },
			 reccommands: {}
		});
}
	
epoq.go({
		tenantId: epoq_tenantId,
		sessionId: epoq_sessionId,
		rules: epoq_d_rules,
		productId: epoq_productId,
		demo: epoq_demoMode,
		success: function (data)
		{
			var recommendations = data['recommendations'];
			if(typeof(recommendations['domain']) != "undefined")
			{
				if( Object.prototype.toString.call(recommendations['domain']) === '[object Array]' ) {
					// two rules set i. e. default;default1
				
					if(typeof(recommendations['domain'][0]) != "undefined") {
						//isagteaser
						var recommendationPids = new Array();
						if (typeof(recommendations['domain'][0]['items']) != "undefined") {
							for(var i=0; i < recommendations['domain'][0]['items'].item.length; i++){
								recommendationPids[i] = recommendations['domain'][0]['items'].item[i]['@id'];
							}
						}
						var recommendationId = recommendations.domain[0].recommendationId['$'];
						addRecommendationsToPage(recommendationPids, recommendationId, data, epoq_section);
					} 
					
					if(typeof(recommendations['domain'][1]) != "undefined") {
						//isagentrypage
						var recommendationPids = new Array();
						
						if (typeof(recommendations['domain'][1]['items']) != "undefined") {
							for(var i=0; i < recommendations['domain'][1]['items'].item.length; i++){
								recommendationPids[i] = recommendations['domain'][1]['items'].item[i]['@id'];
							}
						}
						var recommendationId = recommendations.domain[1].recommendationId['$'];
						addRecommendationsToPage(recommendationPids, recommendationId, data, epoq_section);
					}
				
				} else {
					// one rule set i. e. default
					var recommendationPids = new Array();
					if (typeof(recommendations['domain']['items']) != "undefined") {
						for(var i=0; i < recommendations['domain']['items'].item.length; i++){
							recommendationPids[i] = recommendations['domain']['items'].item[i]['@id'];
						}
					}
					var recommendationId = recommendations.domain.recommendationId['$'];
					addRecommendationsToPage(recommendationPids, recommendationId, data, epoq_section);
				}
				
			}
		}	
});