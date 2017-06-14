This extension exposes external mailing providers to CiviCRM. It is intended
to be generic enough to support the addition of more functionality and providers.

*Currently supported:*
- Silverpop

*Currently supported Functionality*
- Retrieval of mailings from provider & storage in CiviCRM
- Retrieval of per-recipient data relating to the outcomes of sending mailings

*Dependencies*
-  This extension has an external dependency on the Extended Mailing Statistics CiviCRM 
extension if you wish to store mailings data. The omnimailing.load api function
stores statistical data about mailings to the tables created by this extension. This
function does not degrade gracefully, but it can be bypassed. omnimailing.get api
function does not store statistical data, allowing you to store it yourself.

  Subject to resolution of a pull request I am using [my fork](https://github.com/eileenmcnaughton/au.org.greens.extendedmailingstats) of the extension  - I have had some discussions with the Australian Green party about agreeing the changes in principle.

- The extension has internal dependencies on 3 composer packages
1. [Omnimail](https://github.com/gabrielbull/omnimail)   

  Omnimail is a package that exposes multiple mailers in a standardised way.
  The focus of Omnimail was on sending individual mails.
  I discussed with the maintainer & he was open to adding interaction with bulk mailings so
  I worked with him to add a factory class. Pending his consideration of open PRs
  this factory class wrapper is the main thing Omnimail is currently delivering. I have
  proposed interfaces for Mailing & Recipients

  However, I think collaborating towards a standardised interface is a good thing going forwards. 
  In addition I think we could wind up implementing sending of mailings and that would
  leverage the interfaces in that class much more.
  
2. [Omnimail-silverpop](https://github.com/eileenmcnaughton/omnimail-silverpop)  

  This extension makes silverpop available to Omnimail. It provides an interface between the
  standardisation of Omnimail & the underlying silverpop integration package.
  
3. [Silverpop-php-connector](https://github.com/mrmarkfrench/silverpop-php-connector)  
  This extension exposes most of the Silverpop apis. I am currently using my fork, pending
  the maintainer's consideration of [my pull request](https://github.com/mrmarkfrench/silverpop-php-connector/pull/27). He has recently merged [another pull request](https://github.com/mrmarkfrench/silverpop-php-connector/pull/25)
  
*Data storage*
  This extension stores data retrieved in 4 places:
  1. civicrm_mailing table (e.g html & text of emails)
  2. civicrm_mailing_stats table - statistics about emails - provided by extendedmailingstats extension
  3. civicrm_mailing_provider_data - provided by this extension, stores data about mailing recipient
  actions (e.g contact x was sent a mailing on date y or contact z opened a mailing on date u)
  4. civicrm_activity table - separate jobs offer the chance to transfer mailing_provider_data to 
  activities. Depending on size this may only be done for some of the data.
  