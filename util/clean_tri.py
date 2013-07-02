#!/usr/bin/python
#
# clean_tri.py
# Author: Tom Navarro
# Date: SUN, 27 NOV 2011
#
# Description:
# This script will strip unnecessary columns and blank rows from the toxic
# release inventory data set.  The script expects the set as a csv file, and
# will produce a csv file as output.
#
# Usage:
# clean_tri.py [options] infile outfile
#
# Options:
# -a    Retain rows with missing or invalid CAS numbers. By default, these rows
#       are dropped.
# -d    Don't reformat cells.  By default, CAS numbers are hyphenated and ZIPs
#       are truncated.
# -h    Print usage.
# -v    Be verbose.

import sys, csv, getopt, shutil

# Class to make identifying columns easier.
class caCols:
    DOC      = 22
    YEAR     = 0
    LOCID    = 1
    FACILITY = 2
    STREET   = 3
    CITY     = 4
    COUNTY   = 5
    STATE    = 6
    ZIP      = 7
    LAT      = 8
    LONG     = 9
    CHEM     = 23
    CAS      = 24
    CARCI    = 29
    UNITS    = 31
    RELEASE  = 82

#-------------------------------------------------------------------------------
# Name:        formatZip
#
# Summary:     Truncate ZIP codes to five digits.
#
# Arguments:   zipCode - The zip code to truncate, at least five digits long
#
# Returns:     The first five elements in cell.
#
# Constraints: None
#
# Globals:     None
#-------------------------------------------------------------------------------
def formatZip(zipCode):
    return zipCode[0:5]

#-------------------------------------------------------------------------------
# Name:        formatCas
#
# Summary:     Format input as a CAS number (XXXXXXX-XX-X)
#
# Arguments:   casNumber - An unformatted CAS number up to 10 digits long
#
# Returns:     A cas number of the format XXXXXXX-XX-X, with the first segment
#              containing between 2 and 7 digits.
#
# Constraints: This function requires a number that contains at least three
#              digits.  Note that a real CAS number has at least 5 digits.
#
# Globals:     None
#-------------------------------------------------------------------------------
def usage():
    use = """Usage:\n\tclean_tri.py [options] infile outfile\nOptions:
        -a    Retain rows that lack CAS numbers.
        -d    Don't reformat cells.
        -h    Print usage.
        -v    Be verbose.\n"""
    print(use)

def formatCas(casNumber):
    # Trim commas and leading zeroes.
    casNumber = casNumber.replace(",", "").lstrip('0')

    # Format as XXXXXXX-XX-X
    return casNumber[0:-3] + '-' + casNumber[-3:-1] + '-' + casNumber[-1]

def main(argv):
    mRetainRows = False
    mDontReformat = False
    mVerbose = False
    count = 0
    writeCount = 0
    uniqueRows = []
    cols = caCols

    # Process command line args
    try:
        opts, args = getopt.getopt(argv, "adhv")
    except getopt.GetoptError:
        usage()
        sys.exit(2)

    for opt, arg in opts:
        if opt == '-h':
            usage()
            sys.exit()
        elif opt == '-a':
            mRetainRows = True
        elif opt == '-d':
            mDontReformat = True
        elif opt == '-v':
            mVerbose = True

    # Get file names from last two args
    if (len(args) < 2):
        usage()
        sys.exit(1)

    infile = args[-2]
    outfile = args[-1]

    srcFile = open(infile, 'rb')
    destFile = open(outfile, 'wb')
    writer = csv.writer(destFile, delimiter=';')

    if (mVerbose):
        print("Removing duplicate rows.")

    # Iterate through the file and clean rows
    for row in csv.reader(srcFile):
        if row not in uniqueRows:
            uniqueRows.append(row)

    for row in uniqueRows:
        if (count < 1):
                writer.writerow(('\'DOC_CTRL_NUM\'', '\'YEAR\'', '\'LOCATION_SERIAL\'', '\'FACILITY_NAME\'',
                        '\'STREET_ADDRESS\'', '\'CITY\'', '\'COUNTY\'', '\'STATE\'', '\'ZIP\'',
                        '\'LATITUDE\'', '\'LONGITUDE\'', '\'CHEMICAL\'', '\'CAS\'',
                        '\'CARCINOGEN\'', '\'UNITS\'', '\'TOTAL_RELEASE\''))
                count += 1
                if (mVerbose):
                    print("Row " + str(count) + ": Writing header.")
                writeCount += 1
        else:
            if any(field.strip() for field in row): # Strip blank rows
                if not (row[cols.RELEASE] == '0'): # If total release > 0
                    if (row[cols.CAS] == "MIXTURE"): # Only want rows with actual CAS numbers.
                        if (mVerbose):
                            print("Row " + str(count) + ": invalid CAS# (" + row[cols.CAS] + "). Dropping row.")
                        count += 1
                        continue

                    if ((len(row[cols.CAS]) < 5) and (not mRetainRows)): # CAS number too short
                        if (mVerbose):
                            print("Row " + str(count) + ": invalid CAS# (" + row[cols.CAS] + "). Dropping row.")
                        count += 1
                        continue

                    if ((len(row[cols.ZIP]) > 5) and (mDontReformat == False)): # ZIP too long
                        if (mVerbose):
                            print("Row " + str(count) + ": ZIP = " + row[cols.ZIP] + ".  Truncating to 5 digits.")
                        row[cols.ZIP] = formatZip(row[cols.ZIP])

                    if ((len(row[cols.CAS]) >= 5) and (mDontReformat == False)): # Valid CAS, can be reformatted
                        if (mVerbose):
                            print("Row " + str(count) + ": CAS# = " + row[cols.CAS] + ".  Formatting.")
                        row[cols.CAS] = formatCas(row[cols.CAS])

                    if (row[cols.CARCI] == 'YES'): # Ensure booleans have proper format
                        row[cols.CARCI] = 1
                    else:                  # Kludge: Malformed booleans are assumed to be false
                        row[cols.CARCI] = 0

                    if (row[cols.RELEASE].find('.') == -1):
                        row[cols.RELEASE] = row[cols.RELEASE] + ".00"

                    row[cols.CHEM] = row[cols.CHEM].replace('"', '').strip()

                    # Commit the row to the outfile
                    writer.writerow((row[cols.DOC], row[cols.YEAR], row[cols.LOCID], row[cols.FACILITY],
                        row[cols.STREET], row[cols.CITY], row[cols.COUNTY], row[cols.STATE], row[cols.ZIP], row[cols.LAT],
                        row[cols.LONG], row[cols.CHEM], row[cols.CAS], row[cols.CARCI], row[cols.UNITS],
                        row[cols.RELEASE]))
                    writeCount += 1

                elif (mVerbose):
                        print("Row " + str(count) + ": Release amount is zero.  Dropping row.")
            else:
                print("Row " + str(count) + ": Dropping blank row.")
        count += 1

    srcFile.close()
    destFile.close()

    print("Done.\n" + str(count) + " rows processed.\n" + str(writeCount) + " rows written.")

if __name__ == "__main__":
    main(sys.argv[1:])
