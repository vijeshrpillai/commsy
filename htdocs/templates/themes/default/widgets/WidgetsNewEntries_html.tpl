<div class="{literal}${baseClass}{/literal} widget_208">
	<div class="innerWidgetArea">
		<div class="widget_head">
			<h3 class="pop_widget_h3">___COMMON_NEWEST_ENTRIES___</h3>
		</div>
		<div class="widget_head">
			___COMMON_PAGE_ENTRIES___:
			<span class="cursor_pointer" data-dojo-attach-event="onclick:onClickPaging20" data-dojo-attach-point="paging20"><strong>20</strong></span> |
			<span class="cursor_pointer" data-dojo-attach-event="onclick:onClickPaging50" data-dojo-attach-point="paging50">50</span>
			
			<div class="float-right">
				___COMMON_PAGE___: <span data-dojo-attach-point="currentPageNode"></span> / <span  data-dojo-attach-point="maxPageNode"></span>
				<span class="cursor_pointer" data-dojo-attach-event="onclick:onClickPagingFirst">&lt;&lt;</span> |
				<span class="cursor_pointer" data-dojo-attach-event="onclick:onClickPagingPrev">&lt;</span> |
				<span class="cursor_pointer" data-dojo-attach-event="onclick:onClickPagingNext">&gt;</span> |
				<span class="cursor_pointer" data-dojo-attach-event="onclick:onClickPagingLast">&gt;&gt;</span>
			</div>
			
			<div class="clear"></div>
		</div>
		<div class="widget_body" data-dojo-attach-point="widgetBodyNode">
			{i18n tag=COMMON_NEWEST_ENTRIES_IN_ROOMS param1=$environment.username}
			<ul data-dojo-attach-point="itemList">
			</ul>
		</div>
	</div>
</div>