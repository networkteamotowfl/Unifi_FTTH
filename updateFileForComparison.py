outListStringData = ""
ipString = ""
hostString = ""
hostList = []
comparisonStringData = ""
mergedDataArray = []

with open ("C:\\FTTH_FTP\\FTTH\\Out_List.csv", "r") as dhcpDataFile:
    for line in dhcpDataFile.readlines():
        outListStringData = outListStringData + line
        dhcpDataFile.close()
    outListStringData = outListStringData.split(",")


for item in outListStringData:
    if "184.179" in item:
        indexOf = outListStringData.index(item)
        if "ftth.us" in outListStringData[indexOf - 1]:
            ipString = ipString + item
    elif "ftth.us" in item:
        hostString = hostString + item

ipString = ipString.strip('"')
ipString = ipString.split('""')
ipList = ipString

hostString = hostString.splitlines()
hostString.pop()
hostString.pop(0)
for r in hostString:
    if r.startswith('"'):
        r = r[1:]
        r = r[:-8]
        hostList.append(r)
hostList = hostList[::2]

for item in range(0, len(ipList)):
    mergedDataArray.append(ipList[item])
    mergedDataArray.append(hostList[item])




with open ("C:\\FTTH_Tools\\Unifi\\GIT\\fileForComparison.txt", "r") as comparisonDataFile:
    for line in comparisonDataFile.readlines():
        comparisonStringData = comparisonStringData + line
        comparisonDataFile.close()
    comparisonStringData = comparisonStringData.split("\n")

comparisonDataArray = comparisonStringData


for name in range(1, len(mergedDataArray), 2):
    if mergedDataArray[name] in comparisonDataArray:
        if mergedDataArray[name-1] == comparisonDataArray[comparisonDataArray.index(mergedDataArray[name]) + 2]:
            pass
        else:
            comparisonDataArray[comparisonDataArray.index(mergedDataArray[name]) + 2] = (mergedDataArray[name-1])



with open("fileForComparison.txt", "w") as fillInData:
    for data in comparisonDataArray:
        fillInData.write(data + "\n")

fillInData.close()