
General Explanation

    This application was originally written solely for UTA.  By request, it has
    been adapted to be installable outside of UTA.

    Some of the layout of the application (Framework / Middleware) is based on
    the application being part of a larger ecosystem at UTA rather than a
    standalone application.  The organization of the PHP libraries have been
    adapted to make the application a stand-alone application.

    User identity and authentication at UTA is done using a custom Microsoft
    Single Sign-On process which is not applicable outside of UTA.  Two separate
    authencation mechanisms have been provided to hopefully make the application
    usable outside of UTA:


    1) Local password based authentication

        Basic account identity management has been added to the application.
        Minimal testing has been done for functionality since UTA does not use
        this code.  It was provided as an entry point in using the application
        if Single Sign-On is not able to be utilized.

        At the moment, the user will have to do a password reset request to be
        able to login once the accounts is created.  The user can go to the
        Login page, click on Reset Password, and follow the instructions.

        NOTE: After you create an account, you still need grant the user
              permissions.

    2) Microsoft Single Sign-On

        Uses Microsoft identity platform and OAuth 2.0 authorization code flow
        to validate the identity of a user in the organization.  Requires your
        organization to have a Microsoft Tenant ID and an application
        registration in your Azure tenant to allow FL3DPM to make requests to
        the Azure endpoint.

        FL3DPM only uses the OAuth code flow to obtain the identity of the
        individual.  It does not use the Azure tenant for ACL authorization
        within the application.  Permissions in the application are granted
        within the application itself.


Environment

    This appllication was built using:
    1) Apache
    2) MariaDB
    3) PHP

    Although there was no consious decision to specifically tie the PHP code to
    Apache and MariaDB, there are probably some aspects that would require
    modification if you wanted to run FL3DPM with another web server or database
    server.

    The installation of Apache and MariaDB are not documented here.


Installation

    The following instructions walk through setting up FL3DM to use local user
    authentication.  If you want to use SSO, you can intially setup the
    applicaiton using local user authenticaiton, and then change the
    authentication settings in the app/instance/config.php file.

    The instructions are based around having command line access to the web and
    database servers.

    NOTE: An account will be needed for the database server that has permissions
          to create and manage users and databaseses.  For the instructions
          below, "root" is used as a place holder.  Substitute your database
          account as applicable.

    NOTE: The initialization files use a fictional organization University of
          Somewhere (uos.edu).  You will need to adjust the values to match your
          organization.


    - Create a database for the application

        CREATE DATABASE fl3dpm;


    - Import the schema and initial data

        cat DATABASE_INIT | mysql -u root -p fl3dpm


    - Create a user to access the database

        CREATE USER fl3dpm_user@localhost IDENTITIED BY 'password_goes_here';
        GRANT select, insert, update, delete TO fl3dpm_user@localhost;

        NOTE: The database user does not need to be called fl3dpm_user.
              Whatever name you use for the database user account, make sure
              the values are correct in the app/instance/config.php file.

              The MariaDB server can be hosted separately.  Adjust your command
              as needed and place the correct values in the app/instance/config.php
              file.


    - Add User and ACL for administration

        Edit the ACL_INIT file.  Update the admin@uos.edu value to match your
        organization.

        cat ACL_INIT | mysql -u root -p fl3dpm

        NOTE: the password for the admin@uos.edu account is "mypassword".


    - Extract the source code. This example is assuming it is going to be in the
      directory fl3dpm under the Apache Document Root.

        cd <Apache Document Root>
        mkdir fl3dpm
        cd fl3dpm
        unzip fl3dpm.zip


    - Configure the instance

        In the fl3dpm/app/instance directory, there are several files that
        need to be configured.

        - authAccessCheck.example.php
        - config.example.php
        - lib.example.php
        - loginNotice.example.php
        - messages_en.example.php
        - page-header.example.php
        - page-login-header.example.php

        These files are example files used to configure the application instance
        files.  For each file listed above, copy the file to the production
        version name without the .example in the file name.

            cp authAccessCheck.example.php authAccessCheck.php
            cp config.example.php config.php
            cp lib.example.php lib.php
            cp loginNotice.example.php loginNotice.php
            cp messages_en.example.php messages_en.php
            cp page-header.example.php page-header.php
            cp page-login-header.example.php page-login-header.php

        In the future, when you update the code, the production versions of the
        files will NOT be overwritten.

        For each file you copy to the production version, edit the file and make
        the necessary changes.


    - Setup .htaccess

        In the root of the application directory, copy the htaccess.example to
        .htaccess and edit the RewriteBase value to match the root of the
         application.


    - Test Login

        Go to site's main page in a browser, and it should redirect you to the
        login page.  Login using the admin account and the password "mypassword".
        Once logged in, change the admin password by choosing User => Password
        from the menu.


First Steps

    NOTE: During initial application testing, in the app/instance/config.php
          file, it is suggested to set the REDIRECT_ALL_EMAIL setting to TRUE,
          and the REDIRECT_EMAIL_TO setting to your email address.  This will
          prevent accidentally sending out emails while you are testing.

    1) Under Manage => College, create at least one college.

    2) Under Manage => Filament, create at least one filament.

    3) Under Manage => printer, create at least one printer.

    4) Under Manage => User Accounts, Create non-admin user accounts

    5) Under Manage => Access, grant non-admin user accounts permissions
        NOTE: without any permissions granted, users cannot login
        NOTE: if you change permission while someone is logged in,
              the user will need to logout and back in, or wait 5 minutes
              for application permissions cache to timeout.

    6) User Manage => Settings, edit values to match institution.


