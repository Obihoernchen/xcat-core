<?php
/*------------------------------------------------------------------------------
  Produce the page for running commands on the nodes of the cluster
------------------------------------------------------------------------------*/
$TOPDIR = '..';
$expire_time = gmmktime(0, 0, 0, 1, 1, 2038);
setcookie("history", "date;hello.sh", $expire_time);

require_once "$TOPDIR/lib/functions.php";

insertHeader('Run Commands on Nodes', array("$TOPDIR/themes/default.css"),
			array("$TOPDIR/lib/CommandWindow.js", "$TOPDIR/js/prototype.js", "$TOPDIR/js/scriptaculous.js?load=effects", "$TOPDIR/js/window.js"),
			array('machines','dsh'));

?>
<div id=content>
<FORM NAME="dsh_options" onsubmit="checkEmpty();">
<input type="hidden" id="nodename" value=<?php echo @$_REQUEST["noderange"] ?> >
<TABLE class="inner_table" cellspacing=0 cellpadding=5>
  <TBODY>
  	<TR>
  	  <TD colspan="3">
		<?php if (@$_REQUEST["noderange"] == ""){ ?>
  	  	<font class="BlueBack">Run Command on Group:</font>
  	  	<SELECT name=nodegrps id=nodegrpsCboBox class=middle>
  	  	<OPTION value="">Choose ...</OPTION>
  	  	<?php
  	  	$nodegroups = getGroups();
		foreach ($nodegroups as $group) {
				//if($group == $currentGroup) { $selected = 'selected'; } else { $selected = ''; }
				echo "<OPTION value='$group' $selected>$group</OPTION>\n";
		}
		?>
   		</SELECT>

   		<?php }else{ ?>
   		<font class="BlueBack">Run Command on: </font><?php echo @$_REQUEST["noderange"];  } ?>
  	  </TD>
  	</TR>
	<TR>
	  <TD colspan="3">
		<P>Select a previous command from the history, or enter the command and options below. Then click on Run Cmd.</P>
	  </TD>
	</TR>
    <TR>
      <TD colspan="3"><p>
		<INPUT type="button" id="runCmdButton" name="runCmdButton" value="Run Cmd" class=middle onclick="CommandWindow.updateCommandResult()"></p>
      </TD>
    </TR>
    <TR>
      <TD colspan="3"><font class="BlueBack">Command History:</font>
      <SELECT name="history" onChange="_setvars();" class="middle">
      <OPTION value="">Choose ...</OPTION>
      <?php
		$string = @$_COOKIE["history"];
		echo $token = strtok($string, ';');
		echo "<option value=\"" . $token . "\">" . $token . "</option>";

		while (FALSE !== ($token = strtok(';'))) {
   			echo "<option value=\"" . $token . "\">" . $token . "</option>";
		}
	 ?>
     </SELECT>
 	  &nbsp; &nbsp;Selecting one of these commands will fill in the fields below.
 	  </TD>
    </TR>
    <TR>
      <TD colspan="3"><div id="commandResult"></div></TD>
    </TR>
    <TR class=FormTable>
      <TD colspan="3">Command:&nbsp;
      <INPUT size="80" type="text" name="command" id="commandQuery" class="middle"></TD>
    </TR>
    <TR class=FormTable>
      <TD colspan="3" nowrap><INPUT type="checkbox" name="copy_script" id="copyChkBox">
		Copy command to nodes &nbsp;(The command specified above will 1st be copied
      	to /tmp on the nodes and executed from there.)</TD>
    </TR>
    <TR class=FormTable>
      <TD colspan="3" nowrap><INPUT type="checkbox" name="run_psh" id="pshChkBox">
		Use parallel shell (psh) command instead of xdsh.</TD>
    </TR>
    <TR class=FormTable>
      <TD colspan="3"><B>Options:</TD>
    </TR>
    <TR class=FormTable>
      <TD width="37"></TD>
      <TD width="210" valign="top" nowrap><INPUT type="checkbox" name="serial" id="serialChkBox" checked>Streaming mode</TD>
      <TD width="500">Specifies that output is returned as it becomes available from each target, instead of waiting for the command to be completed on a target before returning output from that target.</TD>
    </TR>
    <TR class=FormTable>
      <TD></TD>
      <TD valign="top" nowrap><INPUT type="checkbox" name="monitor" id="monitorChkBox">Monitor</TD>
      <TD>Prints starting and completion messages for each node.  Useful with Streaming mode.</TD>
    </TR>
    <TR class=FormTable>
      <TD></TD>
      <TD valign="top" nowrap><INPUT type="checkbox" name="verify" id="verifyChkBox">Verify</TD>
      <TD>Verifies that nodes are responding before sending the command to them.</TD>
    </TR>
    <TR class=FormTable>
      <TD></TD>
      <TD valign="top" nowrap><INPUT type="checkbox" name="collapse" id="collapseChkBox">Collaspe Identical Output</TD>
      <TD>Automatically pipe the xdsh output into xdshbak which will only display output once for all the nodes that display identical output.  See the xdshbak man page for more info.</TD>
    </TR>
    <TR class=FormTable>
      <TD></TD>
      <TD valign="top" nowrap>Fanout:<INPUT type="text" name="fanout" id="fanoutTxtBox"></TD>
      <TD>The maximum number of nodes the command should be run on concurrently. When the command finishes on 1 of the nodes, it will be started on an additional node (the default is 64).</TD>
    </TR>
    <TR class=FormTable>
      <TD></TD>
      <TD valign="top" nowrap>UserID:<INPUT type="text" name="userID" id="userIDTxtBox"></TD>
      <TD>The user id to use to run the command on the nodes.</TD>
    </TR>
    <TR class=FormTable>
      <TD></TD>
      <TD valign="top" nowrap>Remote Shell:<INPUT type="text" name="rshell" id="rshellTxtBox"></TD>
      <TD>The remote shell program to use to run the command on the nodes. The default is /usr/bin/ssh.</TD>
    </TR>
    <TR class=FormTable>
      <TD></TD>
      <TD valign="top" nowrap>Shell Options:<INPUT type="txt" name="shell_opt" id="shell_optTxtBox"></TD>
      <TD>Options to pass to the remote shell being used.</TD>
    </TR>
    <TR class=FormTable>
      <TD></TD>
      <TD valign="top" nowrap><INPUT type="checkbox" name="ret_code" id="ret_codeChkBox">Code Return</TD>
      <TD>Prints the return code of the (last) command that was run remotely on each node. The return code is appended at the end of the output for each node.</TD>
    </TR>
    <TR><TD colspan="3">
		<font class="BlueBack">Tips:</font>
		<UL>
		  <LI>See the <A href="<?php echo $TOPDIR; ?>/support/doc.php?book=manpages&amp;section=xdsh">xdsh man page</A> for more information about this command.</LI>
		</UL>
	</TD></TR>
  </TBODY>
</TABLE>
</FORM>
<div>
<SCRIPT language="JavaScript">
<!--
// in CSM perl script this portion used to be javascript to get
// and set cookies, now php has handled it

window.onload = function(){window.document.dsh_options.runCmdButton.focus()};
function _setvars(){
	var form = window.document.dsh_options;
	form.command.value = form.history.value;
}
function checkEmpty(){
	var form = window.document.dsh_options;
	var cmd = form.command.value;
	if (cmd.length == 0)
	  {
	    alert('Enter a command before pressing the Run Cmd button.');
	    return false;
	  }
	else { return true; }
}
-->
</SCRIPT>
</BODY>
</HTML>