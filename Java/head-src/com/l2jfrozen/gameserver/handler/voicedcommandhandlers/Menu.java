/*
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/>.
 */
package com.l2jfrozen.gameserver.handler.voicedcommandhandlers;

import javolution.text.TextBuilder;

import com.l2jfrozen.gameserver.handler.IVoicedCommandHandler;
import com.l2jfrozen.gameserver.model.actor.instance.L2PcInstance;
import com.l2jfrozen.gameserver.network.serverpackets.NpcHtmlMessage;

public class Menu implements IVoicedCommandHandler
{
	               private final String[] _voicedCommands =
	               {
	                       "menu"
	               };
      
       @Override
       public boolean useVoicedCommand(String command, L2PcInstance activeChar, String target)
       {
               mainHtml(activeChar);
               return true;
       }
      
       public static void mainHtml(L2PcInstance activeChar)
       {
               NpcHtmlMessage nhm = new NpcHtmlMessage(5);
               TextBuilder tb = new TextBuilder("");
              
               tb.append("<html><head><title>L2Submission</title></head><body>");
               tb.append("<center>");
               tb.append("<body><center><font color=\"003366\" align=\"center\">___________________________________________</font></center><br1>");
               tb.append("<center><img src=\"L2Submission.menu\" width=256 height=90><br1>");
               tb.append("<center><font color=\"003366\" align=\"center\">___________________________________________</font></center>");
               tb.append("<table width=\"250\" cellpadding=\"5\" bgcolor=\"000000\">");
               tb.append("<tr>");
               tb.append("<td valign=\"top\"><font color=\"00FF00\">"+activeChar.getName()+"</font>, use this menu for everything related to your gameplay.<br1></td>");  
               tb.append("</tr>");
               tb.append("</table>");
               tb.append("<table width=\"100\" height=\"12\" bgcolor=\"000000\">");
               tb.append("<tr>");
               tb.append("<td width=\"52\">ON</td>");
               tb.append("<td width=\"16\"><button width=16 height=12 back=\"L2UI_CH3.br_bar1_hp\" fore=\"L2UI_CH3.br_bar1_hp\"></td>");
               tb.append("</tr>");
               tb.append("<tr>");
               tb.append("<td width=\"52\">OFF</td>");
               tb.append("<td width=\"16\"><button width=16 height=12 back=\"L2UI_CH3.br_bar1_mp\" fore=\"L2UI_CH3.br_bar1_mp\"></td>");
               tb.append("</tr>");
               tb.append("</table><br>");
               tb.append("<table width=\"250\" height=\"12\" bgcolor=\"000000\">");
               tb.append("<tr>");
               tb.append("<td width=\"52\">Buff Protection</td>");
               if(activeChar.isBuffProtected())
            	   tb.append("<td width=\"16\"><button action=\"bypass -h buffprot\" width=24 height=12 back=\"L2UI_CH3.br_bar1_hp\" fore=\"L2UI_CH3.br_bar1_hp\"></td>");
               if(!activeChar.isBuffProtected())
            	   tb.append("<td width=\"16\"><button action=\"bypass -h buffprot\" width=24 height=12 back=\"L2UI_CH3.br_bar1_mp\" fore=\"L2UI_CH3.br_bar1_mp\"></td>");
               tb.append("</tr>");
               tb.append("<tr>");
               tb.append("<td width=\"52\">Personal Message Refusal</td>");
               if(activeChar.getMessageRefusal())
            	   tb.append("<td width=\"16\"><button action=\"bypass -h pmref\" width=24 height=12 back=\"L2UI_CH3.br_bar1_hp\" fore=\"L2UI_CH3.br_bar1_hp\"></td>");
               if(!activeChar.getMessageRefusal())
            	   tb.append("<td width=\"16\"><button action=\"bypass -h pmref\" width=24 height=12 back=\"L2UI_CH3.br_bar1_mp\" fore=\"L2UI_CH3.br_bar1_mp\"></td>");
               tb.append("</tr>");
               tb.append("<tr>");
               tb.append("<td width=\"52\">Trade Request Protection</td>");
               
               if(activeChar.isInTradeProt())
            	   tb.append("<td width=\"16\"><button action=\"bypass -h tradeprot\" width=24 height=12 back=\"L2UI_CH3.br_bar1_hp\" fore=\"L2UI_CH3.br_bar1_hp\"></td>");
               
               if(!activeChar.isInTradeProt())
            	   tb.append("<td width=\"16\"><button action=\"bypass -h tradeprot\" width=24 height=12 back=\"L2UI_CH3.br_bar1_mp\" fore=\"L2UI_CH3.br_bar1_mp\"></td>");
               
               tb.append("</tr>");
               tb.append("<tr>");
               tb.append("<td width=\"52\">Soulshot/Spiritshot Effect</td>");
               
               if(activeChar.isSSDisabled())
            	   tb.append("<td width=\"16\"><button action=\"bypass -h ssprot\" width=24 height=12 back=\"L2UI_CH3.br_bar1_hp\" fore=\"L2UI_CH3.br_bar1_hp\"></td>");
               
               if(!activeChar.isSSDisabled())
            	   tb.append("<td width=\"16\"><button action=\"bypass -h ssprot\" width=24 height=12 back=\"L2UI_CH3.br_bar1_mp\" fore=\"L2UI_CH3.br_bar1_mp\"></td>");
               
               tb.append("</tr>");
               tb.append("<tr>");
               tb.append("<td width=\"52\">Party Invite Protection</td>");
               
               if(activeChar.isPartyInvProt())
            	   tb.append("<td width=\"16\"><button action=\"bypass -h partyin\" width=24 height=12 back=\"L2UI_CH3.br_bar1_hp\" fore=\"L2UI_CH3.br_bar1_hp\"></td>");
              
               if(!activeChar.isPartyInvProt())
            	   tb.append("<td width=\"16\"><button action=\"bypass -h partyin\" width=24 height=12 back=\"L2UI_CH3.br_bar1_mp\" fore=\"L2UI_CH3.br_bar1_mp\"></td>");
               
               tb.append("</tr>");
               tb.append("<tr>");
               tb.append("<td width=\"52\">Exp Gain Protection</td>");
               
               if(activeChar.cantGainXP())
            	   tb.append("<td width=\"16\"><button action=\"bypass -h xpnot\" width=24 height=12 back=\"L2UI_CH3.br_bar1_hp\" fore=\"L2UI_CH3.br_bar1_hp\"></td>");
               
               if(!activeChar.cantGainXP())
            	   tb.append("<td width=\"16\"><button action=\"bypass -h xpnot\" width=24 height=12 back=\"L2UI_CH3.br_bar1_mp\" fore=\"L2UI_CH3.br_bar1_mp\"></td>");
               
               tb.append("</tr>");
               tb.append("</table>");
               tb.append("</center>");
               tb.append("</body></html>");
              
               nhm.setHtml(tb.toString());
               activeChar.sendPacket(nhm);
       }
       
       @Override
       public String[] getVoicedCommandList()
       {
               return _voicedCommands;
       }
}