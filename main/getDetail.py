#import system library
import sys
import requests
import urllib.request			#이미지 다운로드
import json
import time
from datetime import datetime
from bs4 import BeautifulSoup	#크롤링

# import user library
sys.path.insert(0, '/home/crawler')
from _lib import config as con
from _lib import cMysql
from _lib import cLogger
from _lib import function as fnc


# set logger
cLog = cLogger.cLogger("getDetail")
logger = cLog.set_logger()


# import def
import defDetail as det


# get input
print("카테고리 : 1 / 단일상품 : 2")
a = int(input())

code = ''
if a == 1 :
	print("카테고리코드를 입력하세요 (CTG001, CTG002, CTG003, CTG004, CTG005, CTG006, CTG007, CTG110) \n 입력값이 없으면 전체데이터를 대상으로 실행합니다 : ")
else :
	print("상품코드를 입력하세요 : ")
#end if

code = str(input())


# set variables
ctg1 = code							# 1depth category code
p_cd = [{'PRODUCT_CD':code}]		# product code


try :
	logger.info("start page crawling : "+ str(datetime.now()))

	if a == 1 :
		rsPrd = det.getProductCode(ctg1)
	else :
		rsPrd = p_cd

	logger.info("total count : "+ str(len(rsPrd)))

	i = 0
	for ds in rsPrd :
		if i > 0 :
			time.sleep(10)
		#end if

		logger.info(str(i) +" loop start : "+ str(datetime.now()))

		p_cd = ds['PRODUCT_CD']

		dsPrd = det.getProductInfo(p_cd)				# 상품정보 가져오기
		logger.info("================ product info ================")
		logger.info("product seq : "+ str(dsPrd['PRODUCT_SEQ']))
		logger.info("product code : "+ str(dsPrd['PRODUCT_CD']))
		logger.info("product name : "+ str(dsPrd['PRODUCT_NM']))
		logger.info("================ product info ================")

		objBs = det.getHtmlObj(p_cd)					# html object 가져오기

		arrMainImg = det.getMainImg(objBs, dsPrd)		# 상품이미지 가져오기
		arrOption = det.getOption(objBs, dsPrd)			# 옵션정보 가져오기
		arrDetailImg = det.getDetailImg(objBs, dsPrd)	# 상품설명이미지 가져오기
		product_nm2 = objBs.find("div", {"class" : "nameR_pd"}).text.strip()		# 상세페이지 상품명 가져오기
		
		# make main data
		dic_main = {}
		dic_main['product_seq'] = dsPrd['PRODUCT_SEQ']
		dic_main["main_img"] = arrMainImg
		dic_main["product_nm2"] = product_nm2
		dic_main["option"] = arrOption
		dic_main["desc_img"] = arrDetailImg

		det.setDetailInfo(dic_main)

		logger.info(str(i) +" loop end : "+ str(datetime.now()))

		i = i + 1
	#end for

	logger.info("end page crawling : "+ str(datetime.now()))
#end try
except Exception as err :
	logger.error("find error : "+ str(err))
#end except