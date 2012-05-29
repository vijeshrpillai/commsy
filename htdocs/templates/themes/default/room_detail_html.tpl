{extends file="room_html.tpl"}

{block name=room_site_actions}
	<a href="commsy.php?cid={$environment.cid}&mod={$environment.module}&fct=detail&iid={$detail.item_id}&mode=print" title="___COMMON_LIST_PRINTVIEW___" target="_blank">
		<img src="{$basic.tpl_path}img/btn_print.gif" alt="___COMMON_LIST_PRINTVIEW___" />
	</a>

	{if $detail.actions.new}
		<a id="create_new" href="commsy.php?cid={$environment.cid}&mod={$environment.module}&fct=edit&iid=NEW" title="___COMMON_NEW_ITEM___">
	    	<img src="{$basic.tpl_path}img/btn_add_new.gif" alt="___COMMON_NEW_ITEM___" />
	    </a>
    {/if}
{/block}

{block name=room_navigation_rubric_title}
	___COMMON_{$room.rubric|upper}_INDEX___
	<strong>___COMMON_{$room.rubric|upper}___ {$detail.browsing_information.position} ___COMMON_OF___ {$detail.browsing_information.count_all}</strong>
{/block}

{block name="room_main_content"}
	<div id="content_with_actions"> <!-- Start content_with_actions -->
		<div class="content_item"> <!-- Start content_item -->
			{block name=room_detail_content}{/block}
		</div> <!-- Ende content_item -->
		{block name=room_detail_footer}{/block}
	</div> <!-- Ende content_with_actions -->
{/block}

{block name=room_right_portlets}
	<div class="portlet_rc">
		<div class="portlet_rc_list">
		{if $detail.browsing_information.paging.first.active}
			<a href="commsy.php?cid={$environment.cid}&mod={$detail.browsing_information.paging.first.module}&fct={$environment.function}{params params=$detail.browsing_information.paging.first.params}">
				<img src="{$basic.tpl_path}img/btn_ar_start2.gif" alt="___COMMON_BROWSE_START_DESC___" />
			</a>
		{else}
			<img src="{$basic.tpl_path}img/btn_ar_start.gif" alt="___COMMON_BROWSE_START_DESC___" />
		{/if}
		{if $detail.browsing_information.paging.prev.active}
			<a href="commsy.php?cid={$environment.cid}&mod={$detail.browsing_information.paging.prev.module}&fct={$environment.function}{params params=$detail.browsing_information.paging.prev.params}">
				<img src="{$basic.tpl_path}img/btn_ar_left2.gif" alt="___COMMON_BROWSE_LEFT_DESC___" />
			</a>
		{else}
			<img src="{$basic.tpl_path}img/btn_ar_left.gif" alt="___COMMON_BROWSE_LEFT_DESC___" />
		{/if}

	    {$detail.browsing_information.position} / {$detail.browsing_information.count_all}
		{if $detail.browsing_information.paging.next.active}
			<a href="commsy.php?cid={$environment.cid}&mod={$detail.browsing_information.paging.next.module}&fct={$environment.function}{params params=$detail.browsing_information.paging.next.params}">
				<img src="{$basic.tpl_path}img/btn_ar_right2.gif" alt="___COMMON_BROWSE_RIGHT_DESC___" />
			</a>
		{else}
			<img src="{$basic.tpl_path}img/btn_ar_right.gif" alt="___COMMON_BROWSE_RIGHT_DESC___" />
		{/if}
		{if $detail.browsing_information.paging.last.active}
			<a href="commsy.php?cid={$environment.cid}&mod={$detail.browsing_information.paging.last.module}&fct={$environment.function}{params params=$detail.browsing_information.paging.last.params}">
				<img src="{$basic.tpl_path}img/btn_ar_end2.gif" alt="___COMMON_BROWSE_END_DESC___" />
			</a>
		{else}
			<img src="{$basic.tpl_path}img/btn_ar_end2.gif" alt="___COMMON_BROWSE_END_DESC___" />
		{/if}
		</div>
		<h2 id="portlet_rc">
		{if $detail.browsing_information.paging.forward_type == 'path'}
			<strong>___TOPIC_PATH___</strong>&nbsp;&nbsp;&nbsp;&nbsp;
		{elseif $detail.browsing_information.paging.forward_type == 'link_item_path'}
			<strong>___CONFIGURATION_TAG_STATUS___</strong>&nbsp;&nbsp;&nbsp;&nbsp;
		{elseif $detail.browsing_information.paging.forward_type == 'search_path'}
			<strong>___COMMON_SEARCH___</strong>&nbsp;&nbsp;&nbsp;&nbsp;
		{else}
			<strong>___COMMON_CHANGE_INDEX_VIEW_LIST___</strong>&nbsp;&nbsp;&nbsp;&nbsp;
		{/if}
		</h2>
		<div class="clear"> </div>

		<div id="dis_navigation">
			{foreach $detail.forward_information as $entry}
				{if !empty($entry.activating_text)}
					<a title="{$entry.activating_text}"href="#">{$entry.position}. {if $entry.is_current}<strong>{/if}{$entry.title|truncate:25:'...':true}{if $entry.is_current}</strong>{/if}</a>
				{else}
					<a href="commsy.php?cid={$environment.cid}&mod={$entry.type}&fct={$environment.function}&iid={$entry.item_id}{params params=$entry.params}">{$entry.position}. {if $entry.is_current}<strong>{/if}{$entry.title|truncate:25:'...':true}{if $entry.is_current}</strong>{/if}</a>
				{/if}
			{/foreach}
			{*{block name=room_right_portlets_navigation}{/block}*}
			<div class="portlet_rc_action">
			{if $detail.browsing_information.paging.forward_type == 'path'}
				<a href="commsy.php?cid={$environment.cid}&mod=topic&fct=detail&iid={$detail.browsing_information.paging.backward_id}" class="context_nav">___COMMON_BACK_TO_PATH___</a>
			{elseif $detail.browsing_information.paging.forward_type == 'link_item_path'}
				<a href="commsy.php?cid={$environment.cid}&mod={$detail.browsing_information.paging.backward_type}&fct=detail&iid={$detail.browsing_information.paging.backward_id}" class="context_nav">___COMMON_BACK_TO_ITEM___</a>
			{elseif $detail.browsing_information.paging.forward_type == 'search_path'}
				<a href="commsy.php?cid={$environment.cid}&mod=search&fct=index&back_to_search=true" class="context_nav">___COMMON_BACK_TO_SEARCH___</a>
			{else}
				<a href="commsy.php?cid={$environment.cid}&mod={$environment.module}&fct=index&back_to_index=true" class="context_nav">___COMMON_BACK_TO_LIST___</a>
			{/if}
			</div>
		</div>
	</div>
{if $room.sidebar_configuration.active.netnavigation}
	<div class="portlet_rc">
	{if $room.netnavigation.edit}
		<a href="{$room.netnavigation.edit_link}" title="{if isset($room.netnavigation.is_community)}{if $room.netnavigation.is_community}___COMMON_ATTACHED_INSTITUTIONS___{else}___COMMON_ATTACHED_GROUPS___{/if}{else}___COMMON_ATTACHED_ENTRIES___{/if}" class="btn_head_rc2">
			<img src="{$basic.tpl_path}img/btn_edit_rc.gif" alt="{if isset($room.netnavigation.is_community)}{if $room.netnavigation.is_community}___COMMON_ATTACHED_INSTITUTIONS___{else}___COMMON_ATTACHED_GROUPS___{/if}{else}___COMMON_ATTACHED_ENTRIES___{/if}" />
		</a>
	{/if}
	<!--
		<a href="" title="{if $h}___COMMON_SHOW___{else}___COMMON_HIDE___{/if}" class="btn_head_rc">
			<img src="{$basic.tpl_path}img/{if $h}btn_open_rc.gif{else}btn_close_rc.gif{/if}" alt="{if $h}___COMMON_SHOW___{else}___COMMON_HIDE___{/if}" />
		</a>
	-->
		<h2>
		{if isset($room.netnavigation.is_community)}
			{if $room.netnavigation.is_community}
				___COMMON_ATTACHED_INSTITUTIONS___ ({$room.netnavigation.count})
			{else}
				___COMMON_ATTACHED_GROUPS___ ({$room.netnavigation.count})
			{/if}
		{else}
			___COMMON_ATTACHED_ENTRIES___ ({$room.netnavigation.count})
		{/if}
		</h2>

		<div class="clear"> </div>
	<!--
		{if $room.netnavigation.edit}
			<a href="{$room.netnavigation.edit_link}" title="{if isset($room.netnavigation.is_community)}{if $room.netnavigation.is_community}___COMMON_ATTACHED_INSTITUTIONS___{else}___COMMON_ATTACHED_GROUPS___{/if}{else}___COMMON_ATTACHED_ENTRIES___{/if}" class="btn_body_rc">
				<img src="{$basic.tpl_path}img/btn_edit_rc.gif" alt="{if isset($room.netnavigation.is_community)}{if $room.netnavigation.is_community}___COMMON_ATTACHED_INSTITUTIONS___{else}___COMMON_ATTACHED_GROUPS___{/if}{else}___COMMON_ATTACHED_ENTRIES___{/if}" />
			</a>
		{/if}
	-->
		<div class="portlet_rc_body">
			<div id="netnavigation">
				<ul>
				{foreach $room.netnavigation.items as $item}
					<li>
						<a target="_self" href="commsy.php?cid={$environment.cid}&mod={$item.module}&fct=detail&iid={$item.linked_iid}&link_item_path={$detail.item_id}" title="{$item.title}">
							<img src="{$basic.tpl_path}img/netnavigation/{$item.img}" title="{$item.title}"/>
						</a>
						<a target="_self" href="commsy.php?cid={$environment.cid}&mod={$item.module}&fct=detail&iid={$item.linked_iid}&link_item_path={$detail.item_id}" title="{$item.title}">
							{$item.link_text|truncate:25:"...":true}
						</a>
					</li>
				{foreachelse}
					___COMMON_NONE___
				{/foreach}
				</ul>
			</div>
		</div>
	</div>
{/if}
{/block}



{block  name=sidebar_buzzwordbox_title}
	___COMMON_ATTACHED_BUZZWORDS___
{/block}
{block name=sidebar_tagbox_treefunction}
	{* Tags Function *}
	{function name=tag_tree level=0}
		<ul>
		{foreach $nodes as $node}
			<li	id="node_{$node.item_id}"
				{if $node.children|count > 0}class="folder"{/if}
				data="url:'commsy.php?cid={$environment.cid}&mod={$environment.module}&fct=index&name=selected&seltag_{$level}={$node.item_id}&seltag=yes'">{if $node.match}<strong>{$node.title}</strong>{else}{$node.title}{/if}
			{if $node.children|count > 0}	{* recursive call *}
				{tag_tree nodes=$node.children level=$level+1}
			{/if}
		{/foreach}
		</ul>
	{/function}
{/block}

{*
{block name=room_detail_footer}
{/block}
*}