from pysnmp.hlapi import *
import random
import string
import json
import sys
import os
import re
import subprocess
from smtplib import SMTP_SSL as SMTP
from email.mime.text import MIMEText


SMTPserver = 'mail.dcmcable.com'
sender =     'ftth@dcmcable.com'
destination = ['networkteam@otowfl.com']
USERNAME = "ftth@dcmcable.com"
PASSWORD = "0t0w1+FTTH"

#DHCP File to read in
outListStringData = ""

#List of IPs, hostnames, macs for comparison
ipString = ""
hostString = ""
macString = ""
ipList = []
hostList = []
macList = []
customerDict = {}
ipListForSetup = []
arrayOfValuesToEmail = []
macAddressToAppend = []
arrayOfReplacementUnifi = []
arrayOfNewUnifi = []

#fileForComparison to read in
dataForComparison = ""

#macAddresses and Hostnames to add to fileListForComparison
hostnamesAndMacsToAddToPreviousList = []


#Send Email
def sendEmail(password, username, usernameNumbers):
    text_subtype = 'plain'


    content= "SSID: " + str(username) +"\n" + "Password: " + str(password)

    subject="Customer WiFi: " + str(usernameNumbers)

    msg = MIMEText(content, text_subtype)
    msg['Subject']=       subject
    msg['From']   = sender # some SMTP servers will do this automatically, not all

    conn = SMTP(SMTPserver)
    conn.set_debuglevel(False)
    conn.login(USERNAME, PASSWORD)
    try:
        conn.sendmail(sender, destination, msg.as_string())
    finally:
        conn.quit()

#Generate random password
def passwordGenerator(size=8, chars=string.ascii_lowercase + string.digits):
    return ''.join(random.choice(chars) for _ in range(size))

#Read DCHPList from C:\FTTH_FTP\FTTH\Out_List.csv, format into array
Out_List = "C:\FTTH_FTP\FTTH\Out_List.csv"

with open(Out_List) as csvFileOutList:
    for line in csvFileOutList.readlines():
        outListStringData = outListStringData + line
    csvFileOutList.close()
outListStringData = outListStringData.split(",")


#Pull mac address, IP, and hostname from outListStringData
for item in outListStringData:
    if "184.179" in item:
        indexOf = outListStringData.index(item)
        if "ftth.us" in outListStringData[indexOf - 1]:
            ipString = ipString + item
            macString = macString + outListStringData[indexOf + 1]
    elif "ftth.us" in item:
        hostString = hostString + item


#Format ipString
ipString = ipString.strip('"')
ipString = ipString.split('""')
ipList = ipString

#Format macString
macString = macString.strip('"')
macString = macString.split('""')
macList = macString

#Format hostString
hostString = hostString.splitlines()
hostString.pop()
hostString.pop(0)
for r in hostString:
    if r.startswith('"'):
        r = r[1:]
        r = r[:-8]
        hostList.append(r)
hostList = hostList[::2]




#Return newly adopted Unifi
arrayOfNewlyAdoptedUnifiIp = ((subprocess.check_output("php Unifi_PHP_ReturnNew.php")).decode()).split()



dataFromLastRunArray = []

#reads in data from last run for comparison
with open("fileForComparison.txt", "r") as dataFromLastRun:
    for line in dataFromLastRun.readlines():
        dataFromLastRunArray.append(line.strip())
    dataFromLastRun.close()





#Find which of the newly adopted ap's are replacements and which are new.
for newUnifiIP in range(0,len(arrayOfNewlyAdoptedUnifiIp),2):
    if arrayOfNewlyAdoptedUnifiIp[newUnifiIP] in dataFromLastRunArray:
        arrayOfReplacementUnifi.append(hostList[ipList.index(arrayOfNewlyAdoptedUnifiIp[newUnifiIP])])
        arrayOfReplacementUnifi.append(arrayOfNewlyAdoptedUnifiIp[newUnifiIP + 1])

    elif arrayOfNewlyAdoptedUnifiIp[newUnifiIP] not in dataFromLastRunArray:
        arrayOfNewUnifi.append(hostList[ipList.index(arrayOfNewlyAdoptedUnifiIp[newUnifiIP])])
        arrayOfNewUnifi.append(arrayOfNewlyAdoptedUnifiIp[newUnifiIP + 1])
        custPassword = passwordGenerator()
        arrayOfNewUnifi.append(custPassword)
        wifiName = ''.join([i for i in hostList[ipList.index(arrayOfNewlyAdoptedUnifiIp[newUnifiIP])] if not i.isdigit()]).lower().title()
        arrayOfNewUnifi.append(wifiName)



#write replacement hostnames to compare to PHP SSID list
with open("hostnamesToCompareReplacement.txt", "w") as hostnameFile:
    for apData in arrayOfReplacementUnifi:
        hostnameFile.write(apData)
        hostnameFile.write("\n")
hostnameFile.close()

#write new hostnames to compare to PHP SSID list
with open("hostnamesToCompareNew.txt", "w") as hostnameFile:
    for apData in arrayOfNewUnifi:
        hostnameFile.write(apData)
        hostnameFile.write("\n")
hostnameFile.close()


#Run Adoption PHP file
subprocess.call("php Unifi_PHP_AdoptReplacements.php")


with open("fileForComparison.txt", "w") as fillInData:

    #comparison and writin for new customers
    for hostname in hostList:
        if hostname not in dataForComparison:
            indexOfUniqueHostname = hostList.index(hostname)
            ipListForSetup.append(ipList[indexOfUniqueHostname])
            macAddressToAppend.append(macList[(hostList.index(hostname))])
            fillInData.write(hostname + "\n")
            fillInData.write(macList[(hostList.index(hostname))] + "\n")
            fillInData.write(ipList[(hostList.index(hostname))] + "\n")
    #run config for new routers
    #routerConfig()

    #comparison and writing for replacements routers
    for hostname in hostList:
        if hostname in dataForComparison:
            if macList[hostList.index(hostname)] != dataForComparison[dataForComparison.index(hostname) + 1]:
                fillInData.write(hostname + "\n")
                fillInData.write(macList[(hostList.index(hostname))] + "\n")
                fillInData.write(ipList[(hostList.index(hostname))] + "\n")

    #comparison and writing on preexisting routers that dont need replacing
    for hostname in hostList:
        if hostname in dataForComparison:
            if macList[hostList.index(hostname)] == dataForComparison[dataForComparison.index(hostname) + 1]:
                fillInData.write(hostname + "\n")
                fillInData.write(macList[(hostList.index(hostname))] + "\n")
                fillInData.write(ipList[(hostList.index(hostname))] + "\n")




#Send email function
for i in range(0,len(arrayOfNewUnifi), 4):
    sendEmail(arrayOfNewUnifi[i+2], arrayOfNewUnifi[i+3], arrayOfNewUnifi[i])

