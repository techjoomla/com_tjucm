package Action;

import java.sql.DriverManager;
import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

import org.openqa.selenium.WebDriver;

import java.sql.ResultSet;
	
public class Database {


			public static void Deleteucmtype(WebDriver driver) throws SQLException, ClassNotFoundException {
				//Loading the required JDBC Driver class
				Class.forName("com.mysql.jdbc.Driver");	
				
				//Creating a connection to the database
				Connection conn = DriverManager.getConnection("jdbc:mysql://localhost:3306/ucmTest","root","root");
				
				//Executing SQL query and fetching the result
				Statement st = conn.createStatement();
				String sqlStr = "truncate table i93ac_tj_ucm_types";
//				String sqlStr = "truncate table hsvi3_tj_ucm_types";
				ResultSet rs = st.executeQuery(sqlStr);
						
			System.out.println("==> deleted data from table ucm type");
			}
			
			public static void Deleteucmdata(WebDriver driver) throws SQLException, ClassNotFoundException {
				//Loading the required JDBC Driver class
				Class.forName("com.mysql.jdbc.Driver");	
				
				//Creating a connection to the database
				Connection conn = DriverManager.getConnection("jdbc:mysql://localhost:3306/ucmTest","root","root");
				
				//Executing SQL query and fetching the result
				Statement st = conn.createStatement();
				String sqlStr = "truncate table i93ac_tj_ucm_data";
//				String sqlStr = "truncate table hsvi3_tj_ucm_data";
				ResultSet rs = st.executeQuery(sqlStr);
						
			System.out.println("==> deleted data from table ucm data");
			}
			
			public static void DeleteTjfieldsGroups(WebDriver driver) throws SQLException, ClassNotFoundException {
				//Loading the required JDBC Driver class
				Class.forName("com.mysql.jdbc.Driver");	
				
				//Creating a connection to the database
				Connection conn = DriverManager.getConnection("jdbc:mysql://localhost:3306/ucmTest","root","root");
				
				//Executing SQL query and fetching the result
				Statement st = conn.createStatement();
				String sqlStr = "truncate table i93ac_tjfields_groups";
//				String sqlStr = "truncate table hsvi3_tjfields_groups";
				ResultSet rs = st.executeQuery(sqlStr);
						
			System.out.println("==> deleted data from table tj field group");
			
			}
			public static void DeleteTjfieldsOption(WebDriver driver) throws SQLException, ClassNotFoundException {
				//Loading the required JDBC Driver class
				Class.forName("com.mysql.jdbc.Driver");	
				
				//Creating a connection to the database
				Connection conn = DriverManager.getConnection("jdbc:mysql://localhost:3306/ucmTest","root","root");
				
				//Executing SQL query and fetching the result
				Statement st = conn.createStatement();
				String sqlStr = "truncate table i93ac_tjfields_options";
//				String sqlStr = "truncate table hsvi3_tjfields_options";
				ResultSet rs = st.executeQuery(sqlStr);
						
			System.out.println("==> deleted data from table tj field options");
			
			}
			public static void DeleteTjfields(WebDriver driver) throws SQLException, ClassNotFoundException {
				//Loading the required JDBC Driver class
				Class.forName("com.mysql.jdbc.Driver");	
				
				//Creating a connection to the database
				Connection conn = DriverManager.getConnection("jdbc:mysql://localhost:3306/ucmTest","root","root");
				
				//Executing SQL query and fetching the result
				Statement st = conn.createStatement();
				String sqlStr = "truncate table i93ac_tjfields_fields";
//				String sqlStr = "truncate table hsvi3_tjfields_fields";
				ResultSet rs = st.executeQuery(sqlStr);
						
			System.out.println("==> deleted data from table tj field options");
			
			}
	}
	