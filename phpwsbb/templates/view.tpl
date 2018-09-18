<a name="{ID}"></a>
<table border="0" width="100%" cellspacing="1" cellpadding="4" style="clear:both">
  <tr{BG}>
    <td width="140" valign="top">
    	<b>{POSTER}</b><br />
      <div class="smalltext">
        <!-- BEGIN RANK -->
        {RANK}<br />
        <!-- END RANK -->
        <!-- BEGIN AVATAR -->
        {AVATAR}<br />
        <!-- END AVATAR -->
        <br />
        <!-- BEGIN POSTCOUNT -->
        {POSTS}<br />
        <!-- END POSTCOUNT -->
        <!-- BEGIN LOCATION -->
        {FROM}<br />
        <!-- END LOCATION -->
        <br />
        <!-- BEGIN IPADDR -->
        {IP_TEXT}: {IPADDR}<br />
        <!-- END IPADDR -->
        <!-- BEGIN BAN_IPADDR -->
        {BAN_IP}<br />
        <!-- END BAN_IPADDR -->
        <!-- BEGIN BAN_USER -->
        {BAN_USERNAME}<br />
        <!-- END BAN_USER -->
        <!-- BEGIN NOTE -->
        {NOTE}<br />
        <!-- END NOTE -->
        <!-- BEGIN EDIT_SETTINGS -->
        {EDIT_USER}<br />
        <!-- END EDIT_SETTINGS -->
      </div>
    </td>
    <td valign="top">
      <b>{SUBJECT}</b><br />
      <div class="smalltext" style="border-bottom: 1px solid; margin-bottom: 1em">
      {CREATED}
      </div>
      {BODY}
      <!-- BEGIN SIG -->	
      <div class="smalltext" style="border-top: 1px solid; margin-top: 2em;">
      {SIGNATURE}
      </div>
      <!-- END SIG -->
	  <!-- BEGIN EDITED -->
      <div class="smalltext" style="border-top: 1px solid; margin-top: 2em;">
	  {EDITED}
	  </div>
	  <!-- END EDITED -->
    </td>
  </tr>
  <tr{BG}>
    <td>{NAV}</td>
    <td>
    <!-- BEGIN QUOTE -->
    {QUOTE}
    <!-- END QUOTE -->
    <!-- BEGIN REPORT -->
    &nbsp;{REPORT}
    <!-- END REPORT -->
    <!-- BEGIN EDIT -->
    &nbsp;{EDIT}
    <!-- END EDIT -->
    <!-- BEGIN FORK -->
    &nbsp;{FORK}
    <!-- END FORK -->
    <!-- BEGIN DELETE -->
    &nbsp;{DELETE}
    <!-- END DELETE -->
    <!-- BEGIN HIDE -->
    &nbsp;{HIDE}
    <!-- END HIDE -->
	</td>
  </tr>
</table>
