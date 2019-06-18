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
package com.l2jfrozen.gameserver.managers;

import java.io.File;
import java.io.FileInputStream;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Properties;
import java.util.logging.Logger;

import javolution.util.FastList;
import javolution.util.FastMap;

import com.l2jfrozen.util.database.L2DatabaseFactory;
import com.l2jfrozen.gameserver.model.L2Clan;
import com.l2jfrozen.gameserver.model.L2ClanMember;
import com.l2jfrozen.gameserver.model.actor.instance.L2PcInstance;

/**
 * @author  Setekh
 * Doing your mom while ur reading
 */
public class SiegeRewardManager
{
       // Singleton
       private static SiegeRewardManager _instance;
       static Logger _log = Logger.getLogger(SiegeRewardManager.class.getName());
      
       // Config
       public static boolean ACTIVATED_SYSTEM;
       public static boolean REWARD_ACTIVE_MEMBERS_ONLY;

       // Constant
       private final FastList<RewardInfoz> _list;
       private final FastList<RewardInfozCl> _cllist;
       private final FastMap<Integer, FastList<ToReward>> _toReward; // Offline players that didn't get rewarded. =( poor guys, But they'll have a surprise
      
       @SuppressWarnings("unused")
	public SiegeRewardManager()
       {
               _list = new FastList<RewardInfoz>();
               _cllist = new FastList<RewardInfozCl>();
               _toReward = new FastMap<Integer, FastList<ToReward>>();
               _log.info("SiegeRewardManager: Activated.");
       }

       public static SiegeRewardManager getInstance()
       {
               if(_instance == null)
               {
                       _instance = new SiegeRewardManager();
                       _instance.loadConfigs();
                       _instance.loadOfflineMembers();
               }
              
               return _instance;
       }
      
       @SuppressWarnings("null")
	private void loadOfflineMembers()
       {
               // Mysql connector
               Connection con = null;
              
               try
               {
                       con = L2DatabaseFactory.getInstance().getConnection();
                       PreparedStatement st = con.prepareStatement("select charId, itemId, count, castle_name, rewarded from reward_list");
                       ResultSet rs = st.executeQuery();
                      
                       while(rs.next())
                       {
                               int charId = rs.getInt("charId");
                               int itemId = rs.getInt("itemId");
                               int count = rs.getInt("count");
                               String castle_name = rs.getString("castle_name");
                               boolean rewarded = rs.getBoolean("rewarded");
                              
                               if(rewarded)
                               {
                                       deleteRewarded(charId, itemId);
                                       continue;
                               }
                              
                               ToReward tr = new ToReward();
                               tr.charId = charId;
                               tr.itemId = itemId;
                               tr.count = count;
                               tr.castleName = castle_name;
                      
                               if(!_toReward.containsKey(charId))
                               {
                                       try // prevent errors
                                       {
                                               _toReward.put(charId, new FastList<ToReward>());
                                       }
                                       finally
                                       {
                                               _toReward.get(charId).add(tr);
                                       }
                               }
                               else
                                       _toReward.get(charId).add(tr);
                              
                       }
                      
                       rs.close();
                       st.close();
               }
               catch(Exception e)
               {
                       e.printStackTrace();
               }
               finally
               {
                       try
                       {
                               con.close();
                       }
                       catch (SQLException e)
                       {
                               e.printStackTrace();
                       }
               }
       }

       @SuppressWarnings("null")
	public void deleteRewarded(int charId, int itemId)
       {
               // Mysql connector
               Connection con = null;
              
               try
               {
                       con = L2DatabaseFactory.getInstance().getConnection();
                       PreparedStatement st = con.prepareStatement("delete from reward_list where charId=? and itemId=?");
                       st.setInt(1, charId);
                       st.setInt(2, itemId);
                       st.execute();
                       st.close();
               }
               catch(Exception e)
               {
                       e.printStackTrace();
               }
               finally
               {
                       try
                       {
                               con.close();
                       }
                       catch (SQLException e)
                       {
                               e.printStackTrace();
                       }
               }
       }
      
       private void loadConfigs()
       {
               try
               {
                       Properties prop = new Properties();
                       prop.load(new FileInputStream(new File("./config/functions/l2submission.properties")));
                      
                       ACTIVATED_SYSTEM = Boolean.parseBoolean(prop.getProperty("ActivateSystem", "false"));
                       REWARD_ACTIVE_MEMBERS_ONLY = Boolean.parseBoolean(prop.getProperty("RewardOnlineOnly", "false"));
                      
                       if(ACTIVATED_SYSTEM)
                       {
                               String[] splitz = prop.getProperty("RewardInfo").split(";");
                              
                               for(String str: splitz)
                               {
                                       String[] splits = str.split(",");
                                       _list.add(new RewardInfoz(splits));
                               }
                       }
                       if(ACTIVATED_SYSTEM)
                       {
                               String[] splitz = prop.getProperty("RewardClInfo").split(";");
                              
                               for(String str: splitz)
                               {
                                       String[] splits = str.split(",");
                                       _cllist.add(new RewardInfozCl(splits));
                               }
                       }
               }
               catch(Exception e)
               {
                       e.printStackTrace();
               }
               finally
               {
                       _log.info("SiegeRewardManager Loaded: "+_list.size()+" and "+_cllist.size()+" Reword Item(s).");
              }
       }

       @SuppressWarnings("null")
	public void storeDataBase(int charId, String castleName)
       {
               // Mysql connector
               Connection con = null;
              
               try
               {
                       con = L2DatabaseFactory.getInstance().getConnection();
                      
                       for(RewardInfoz rewz : _list)
                       {
                               PreparedStatement st = con.prepareStatement("replace into reward_list values(?,?,?,?,?)");
                               st.setInt(1, charId);
                               st.setInt(2, rewz.getItemId());
                               st.setInt(3, rewz.getItemCount());
                               st.setString(4, castleName);
                               st.setInt(5, 0);
                               st.execute();
                               st.close();
                       }
               }
               catch(Exception e)
               {
                       e.printStackTrace();
               }
               finally
               {
               try
                       {
                       con.close();
                       }
                       catch (SQLException e)
                       {
                               e.printStackTrace();
                       }
               }
       }
      
       public void processWorldEnter(L2PcInstance activeChar)
       {
               if(_toReward.containsKey(activeChar.getObjectId()))
               {
                       String castleName = "";
                      
                       for(ToReward tr :_toReward.get(activeChar.getObjectId()))
                       {
                       activeChar.addItem("SiegeReward", tr.itemId, tr.count, activeChar, true);
                               castleName = tr.castleName;
                               tr.rewarded = true;
                       }
                       activeChar.sendMessage("Congratulations! You have been rewarded for the "+ castleName+ " siege victory!");
               }
       }
      
       public class ToReward { String castleName; int charId, itemId, count; boolean rewarded; }
      
       public class RewardInfoz
       {
               // Constants
               private final int _itemId;
               private final int _itemCount;
              
               public RewardInfoz(String...strings)
               {
                       _itemId = Integer.parseInt(strings[0]);
                       _itemCount = Integer.parseInt(strings[1]);
               }

               /**
                * @return Returns the itemId.
                */
               public int getItemId()
               {
                       return _itemId;
               }

               /**
                * @return Returns the itemCount.
                */
               public int getItemCount()
               {
                       return _itemCount;
               }
              
       }
      
       public class RewardInfozCl
       {
               // Constants
               private final int _itemId;
               private final int _itemCount;
              
               public RewardInfozCl(String...strings)
               {
                       _itemId = Integer.parseInt(strings[0]);
                       _itemCount = Integer.parseInt(strings[1]);
               }

               /**
                * @return Returns the itemId.
                */
               public int getItemId()
               {
                       return _itemId;
               }

               /**
                * @return Returns the itemCount.
                */
               public int getItemCount()
               {
                       return _itemCount;
               }
              
       }

       public void notifySiegeEnded(L2Clan clan, String castleName)
       {
               for(L2ClanMember member : clan.getMembers())
               {
                       if(member.isOnline())
                       {
                               L2PcInstance activeChar = member.getPlayerInstance();
                               if (!(clan.getLeader()==member))
                               {
                               for(RewardInfoz tr : _list)
                                       activeChar.addItem("SiegeReward", tr.getItemId(), tr.getItemCount(), activeChar, true);

                               activeChar.sendMessage("Congratulations! You have been rewarded for the " +castleName+ " siege victory!");
                               }
                               else
                               {
                                       for(RewardInfozCl trCl : _cllist)
                                               activeChar.addItem("SiegeReward", trCl.getItemId(), trCl.getItemCount(), activeChar, true);

                                       activeChar.sendMessage("Congratulations! You have been rewarded for the " +castleName+ " siege victory!");
                               }
                       }
                       else if (!member.isOnline() && !(clan.getLeader()==member) )
                               storeDataBase(member.getObjectId(), castleName);
               }
       }
      
}