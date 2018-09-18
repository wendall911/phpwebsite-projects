{START_FORM}
<h3>{STATUS_TITLE}</h3>
<label>{SETTINGS_BBOFFLINE} {BBOFFLINE_TEXT}</label>&#160;{BBOFFLINE_HELP}<br />

<h3>{ANON_TITLE}</h3>
<label>{SETTINGS_ALLOW_ANON_POSTS} {ANONPOSTS_TEXT}</label><br />
<label>{SETTINGS_ALLOW_ANON_VIEW} {ANONVIEW_TEXT}</label><br />

<h3>{STATS_TITLE}</h3>
<label>{SETTINGS_VIEWS} {VIEWS_TEXT}</label>&#160;{VIEWS_HELP}<br />
<label>{SETTINGS_VIEWS_LOWPRI} {VIEWS_LOWPRI_TEXT}</label>&#160;{VIEWS_LOWPRI_HELP}<br />

<h3>{MONITORS_TITLE}</h3>
<label>{ADMIN_EMAIL_TEXT}: {SETTINGS_ADMIN_EMAIL}</label>&#160;{ADMIN_EMAIL_HELP}<br />
<label>{SETTINGS_MONITOR_POSTS} {MONITOR_POSTS_TEXT}</label>&#160;{MONITOR_POSTS_HELP}<br />
<label>{SETTINGS_ALLOW_USER_MONITORS} {ALLOW_USER_MONITORS_TEXT}</label><br />
<label for="SETTINGS_email_text">{EMAIL_TEXT_TEXT}</label>:&#160;
{EMAIL_TEXT_HELP}<br />{SETTINGS_EMAIL_TEXT}<br />

<h3>{BLOCKS_TITLE}</h3>
<label>{SETTINGS_SHOWFORUMSBLOCK} {SHOWFORUMSBLOCK_TEXT}</label><br />
<label>{FORUMSBLOCKTITLE_TEXT}: {SETTINGS_FORUMSBLOCKTITLE}</label><br />
<label>{SETTINGS_SHOWLATESTTHREADSBLOCK} {SHOWLATESTTHREADSBLOCK_TEXT}</label><br />
<label>{LATESTTHREADSBLOCKTITLE_TEXT}: {SETTINGS_LATESTTHREADSBLOCKTITLE}</label><br />
<label>{SETTINGS_MAXLATESTTHREADS} {MAXLATESTTHREADS_TEXT}</label><br />

<h3>{CATEGORIES_TITLE}</h3>
<label>{SETTINGS_SHOW_CATEGORIES} {SHOW_CATEGORIES_TEXT}</label><br />

<h3>{AVATARS_TITLE}</h3>
<label>{SETTINGS_USE_AVATARS} {USE_AVATARS_TEXT}</label><br />
<label>{SETTINGS_USE_OFFSITE_AVATARS} {USE_OFFSITE_AVATARS_TEXT}</label><br />

<h3>{PERSONAL_TITLE}</h3>
<label>{SETTINGS_USE_SIGNATURES} {USE_SIGNATURES_TEXT}</label><br />

<h3>{RANK_TABLE_TEXT}</h3>
{RANK_TABLE_HELP}<br /><br />
<table width="100%" cellspacing="1" cellpadding="4">
  <tr class="bg_medium">
    <th width="60%" scope="col">{RANK_TITLE_LABEL}</th>
    <th valign="middle" style="text-align:center" scope="col"><b>{RANK_MIN_LABEL}</b></th>
    <th valign="middle" style="text-align:center" scope="col"><b>{RANK_SPECIAL_LABEL}</b></th>
  </tr>
  <tr>
    <td colspan="3"><b>{RANK_MEMBER_TITLE}</b></td>
  </tr>
  {RANK_MEMBER_ROWS}
  <tr>
    <td colspan="3"><b>{RANK_SPECIAL_TITLE}</b></td>
  </tr>
  {RANK_SPECIAL_ROWS}
  <tr>
    <td colspan="3"><b>{RANK_NEW_TITLE}</b></td>
  </tr>
  {RANK_NEW_ROW}
</table>
<br />
{SETTINGS_SAVE}
{END_FORM}
