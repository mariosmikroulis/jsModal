/* This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 *
 * http://www.gnu.org/copyleft/gpl.html
 */
package com.l2jfrozen.gameserver.handler.voicedcommandhandlers;

import javolution.text.TextBuilder;

import com.l2jfrozen.gameserver.handler.IVoicedCommandHandler;
import com.l2jfrozen.gameserver.handler.admincommandhandlers.AdminSurvey;
import com.l2jfrozen.gameserver.model.actor.instance.L2PcInstance;
import com.l2jfrozen.gameserver.network.serverpackets.NpcHtmlMessage;

/**
 *
 * @author  Elfocrash
 *
 */
public class Survey implements IVoicedCommandHandler
{
       private static final String[] VOICED_COMMANDS = { "survey" };
      
       @Override
       public boolean useVoicedCommand(String command, L2PcInstance activeChar, String target)
       {
               if (command.equals("survey"))
               {
                       if(AdminSurvey.running == false)
                       {
                               activeChar.sendMessage("There is no survey running now");
                               return false;
                       }
                      
                       if(activeChar.hasVotedSurvey())
                       {
                               activeChar.sendMessage("You already voted for that survey.");
                               return false;
                       }
                      
                       if(AdminSurvey.running == true)
                               mainHtml(activeChar);
               }
              
              
              
               return true;
       }
      
       private static void mainHtml(L2PcInstance activeChar)
       {
               NpcHtmlMessage nhm = new NpcHtmlMessage(5);
               TextBuilder tb = new TextBuilder("");
              
               tb.append("<html><title>L2Submission!</title><body><center><br><font color=\"003366\" align=\"center\" value=\"___________________________________________</font><img src=L2Submission.servername height=90 width=256><br1><font color=\"003366\" align=\"center\" value=\"___________________________________________</font><br1>");
               tb.append("<center>");
               tb.append("<table width=\"250\" cellpadding=\"5\" bgcolor=\"000000\" value=\"");
               tb.append("<tr>");
               tb.append("<td width=\"45\" valign=\"top\" align=\"center\" value=\"<img src=\"L2ui_ch3.menubutton4\" width=\"38\" height=\"38\" value=\"</td>");
               tb.append("<td valign=\"top\" value=\"<font color=\"FF6600\" value=\"Survey</font>");  
               tb.append("<br1><font color=\"FF6600\" value=\""+activeChar.getName()+"</font>, use this form in order to give us feedback.<br1></td>");
               tb.append("</tr>");
               tb.append("</table>");
               tb.append("</center>");
               tb.append("<center>");

                       tb.append("<font color=\"FF6600\" value=\"The question set is:<br>");
                       tb.append("<font color=\"FF0000\" value=\"" + AdminSurvey.quest+"</font>");
               tb.append("<br><font color=\"FF6600\" value=\"Choose an answer.");
               tb.append("<table width=\"300\" height=\"20\" value=\"");
               tb.append("<tr>");
               tb.append("<td align=\"center\" width=\"40\" value=\"Answer 1:</td>");
                       tb.append("<td align=\"center\" width=\"150\" value=\"<font color=\"FF0000\" value=\"" + AdminSurvey.ans1 + "</font></td>");
                       tb.append("<td align=\"center\" value=\"<button width=75 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h survey_vote1\" value=\"Vote\"></td>");
      
               tb.append("</tr>");
               tb.append("<tr>");
               tb.append("<td align=\"center\" width=\"40\" value=\"Answer 2:</td>");
                       tb.append("<td align=\"center\" width=\"150\" value=\"<font color=\"FF0000\" value=\"" + AdminSurvey.ans2 + "</font></td>");
                       tb.append("<td align=\"center\" value=\"<button width=75 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h survey_vote2\" value=\"Vote\"></td>");
              
               tb.append("</tr>");
               if(AdminSurvey.mode == 2)
               {
                       tb.append("<tr>");
                       tb.append("<td align=\"center\" width=\"40\" value=\"Answer 3:</td>");
                               tb.append("<td align=\"center\" width=\"150\" value=\"<font color=\"FF0000\" value=\"" + AdminSurvey.ans3 + "</font></td>");
                               tb.append("<td align=\"center\" value=\"<button width=75 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h survey_vote3\" value=\"Vote\"></td>");
                      
                       tb.append("</tr>");
               }
               if(AdminSurvey.mode == 3)
               {
                       tb.append("<tr>");
                       tb.append("<td align=\"center\" width=\"40\" value=\"Answer 3:</td>");
                               tb.append("<td align=\"center\" width=\"150\" value=\"<font color=\"FF0000\" value=\"" + AdminSurvey.ans3 + "</font></td>");
                               tb.append("<td align=\"center\" value=\"<button width=75 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h survey_vote3\" value=\"Vote\"></td>");
                      
                       tb.append("</tr>");
                       tb.append("<tr>");
                       tb.append("<td align=\"center\" width=\"40\" value=\"Answer 4:</td>");
                               tb.append("<td align=\"center\" width=\"150\" value=\"<font color=\"FF0000\" value=\"" + AdminSurvey.ans4 + "</font></td>");
                               tb.append("<td align=\"center\" value=\"<button width=75 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h survey_vote4\" value=\"Vote\"></td>");
                      
                       tb.append("</tr>");
               }
               if(AdminSurvey.mode == 4)
               {
                       tb.append("<tr>");
                       tb.append("<td align=\"center\" width=\"40\" value=\"Answer 3:</td>");

                               tb.append("<td align=\"center\" width=\"150\" value=\"<font color=\"FF0000\" value=\"" + AdminSurvey.ans3 + "</font></td>");
                               tb.append("<td align=\"center\" value=\"<button width=75 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h survey_vote3\" value=\"Vote\"></td>");

                       tb.append("</tr>");
                       tb.append("<tr>");
                       tb.append("<td align=\"center\" width=\"40\" value=\"Answer 4:</td>");

                               tb.append("<td align=\"center\" width=\"150\" value=\"<font color=\"FF0000\" value=\"" + AdminSurvey.ans4 + "</font></td>");
                               tb.append("<td align=\"center\" value=\"<button width=75 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h survey_vote4\" value=\"Vote\"></td>");
                      
                       tb.append("</tr>");
                       tb.append("<tr>");
                       tb.append("<td align=\"center\" width=\"40\" value=\"Answer 5:</td>");
                               tb.append("<td align=\"center\" width=\"150\" value=\"<font color=\"FF0000\" value=\"" + AdminSurvey.ans5 + "</font></td>");
                               tb.append("<td align=\"center\" value=\"<button width=75 height=21 back=\"L2Submission.button2\" fore=\"L2Submission.button1\" align=\"center\" action=\"bypass -h survey_vote5\" value=\"Vote\"></td>");
                      
                       tb.append("</tr>");
               }
               tb.append("</table><br>");
               tb.append("</center>");
               tb.append("<font color=\"003366\" align=\"center\">___________________________________________</font></body></html>");
              
               nhm.setHtml(tb.toString());
               activeChar.sendPacket(nhm);
       }
      
       @Override
       public String[] getVoicedCommandList()
       {
               return VOICED_COMMANDS;
       }
}