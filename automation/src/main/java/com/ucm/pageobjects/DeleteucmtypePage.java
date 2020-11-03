package com.ucm.pageobjects;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;

import org.apache.log4j.Logger;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.PageFactory;

import com.ucm.config.BaseClass;

/**
 * This is Page Class for deleting the ucm data . It contains all the elements and actions
 * related to deleting ucm data.
 * 
 */

public class DeleteucmtypePage extends BaseClass{

	static Logger log = Logger.getLogger(DeleteucmtypePage.class);


	public DeleteucmtypePage deleteucmtype() throws SQLException, ClassNotFoundException {

			//Loading the required JDBC Driver class
			Class.forName("com.mysql.jdbc.Driver");	
			//Creating a connection to the database
			Connection conn = DriverManager.getConnection("jdbc:mysql://ucmjuly.cloudaccess.host:3306/xfhmkojr","xfhmkojr","3:E66Sy3Xqof+E");
			//Executing SQL query and fetching the result
			Statement st = conn.createStatement();
			
			String ucmtype = "truncate table kxv_tj_ucm_types";
			ResultSet rs = st.executeQuery(ucmtype);
			logger.pass("ucm type deleted");
			
			String ucmdata = "truncate table kxv_tj_ucm_data";
			ResultSet rs1 = st.executeQuery(ucmdata);
			logger.pass("ucm data deleted");
			
			String fieldgroup = "truncate table kxv_tjfields_groups";
			ResultSet rs2 = st.executeQuery(fieldgroup);
			logger.pass("ucm tj fields deleted");
			
			String fieldoption = "truncate table kxv_tjfields_options";
			ResultSet rs3 = st.executeQuery(fieldoption);
			logger.pass("ucm tj fields options deleted");
			
			String fieldsfields = "truncate table kxv_tjfields_fields";
			ResultSet rs4 = st.executeQuery(fieldsfields);
			logger.pass("ucm tj fields fields deleted");
			
			String cat = "Delete FROM kxv_categories WHERE extension LIKE '%com_tjucm.pomform%' ";
			int rs5 = st.executeUpdate(cat);
			logger.pass("Deleted the data in category table");
			
		
			return this; 
	} 

}
