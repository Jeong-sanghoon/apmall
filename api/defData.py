#import system library
import sys
import requests
import urllib.request			# 파일다운로드
import json
from datetime import datetime
from bs4 import BeautifulSoup	# 크롤링

# import user library
sys.path.insert(0, '/home/crawler')
from _lib import config as con
from _lib import cMysql
from _lib import cLogger
from _lib import function as fnc


#set variables
now_dt = con._NOW_DT


# set class
cLog = cLogger.cLogger("defData")
logger = cLog.set_logger()


# html요청 및 bs4 오브젝트 처리
def getHtmlObj(url) :
	# get html and bs4 parsing
	req = requests.get(url)
	html = req.text
	soup = BeautifulSoup(html, 'html.parser')

	return soup
#end def


# 상품코드 가져오기
def getProductCode(obj) :
	try :
		p_cd = obj.find('form', {'id':'frm'}).find('input', {'id':'i_sProductcd'}).get('value')
		
		return p_cd
	#end try
	except Exception as err:
		logger.error("find error : "+ str(err))
	#end except
#end def


# 상품메인이미지 가져오기
def getMainImg(obj, brand_cd) :
	try :
		list_main_img = obj.find("ul", {"class" : "dR_img"}).find_all('li')

		arr_main_img = []
		for ds_img in list_main_img:
			isVod = fnc.IS_ELEMENT(ds_img.find('div', {'class' : 'video_inner'}))
			
			if isVod == False :
				img_url = ds_img.find('img').get('src')		# 이미지 url추출

				fullfilename = fnc.GET_FILENAME_FROM_URL(img_url)			# 전체파일명

				#fnc.MAKE_FOLDER(con._UPLOAD_DIR +'/'+ dsPrd['v_brandcd'])

				cust_filename = brand_cd +"/"+ fullfilename
				urllib.request.urlretrieve(img_url, con._UPLOAD_DIR +"/"+ cust_filename)		# 다운로드

				arr_main_img.append(cust_filename)
			#end if
		#end for

		return arr_main_img
	#end try
	except Exception as err:
		logger.error("find error : "+ str(err))
	#end except
#end def


# 썸네일 생성
def setThumb(arrMainImg, brand_cd) :
	try :
		cust_filename = arrMainImg[0]
		fullfilename = fnc.GET_FILENAME_FROM_URL(img)			# 전체파일명
		filename = fnc.GET_FILENAME(fullfilename) +'_thumb'
		ext = fnc.GET_EXT(fullfilename)
		cust_savefile = brand_cd +'/'+ filename +'.'+ ext

		src = con._UPLOAD_DIR +"/"+ cust_filename
		savefile = con._UPLOAD_DIR +"/"+ cust_savefile
		arr_size = [356, 356]
		fnc.MAKE_THUMB(src, savefile, arr_size)

		return cust_savefile
	#end try
	except Exception as err:
		logger.error("find error : "+ str(err))
	#end except
#end def


# 옵션정보 가져오기
def getOption(obj, product_nm, price) :
	try :
		arr_option = []
		is_option = fnc.IS_ELEMENT(obj.find("div", {"id" : "listOptScroll1"}))

		if is_option == True :
			# 옵션이 있을때
			list_option = obj.find("div", {"id" : "listOptScroll1"}).find_all("dl", {"class" : "op_sell"})

			for obj_option in list_option :
				dic_option = {}
				optname = obj_option.find("dt").text.strip()
				optname = optname.replace("(일시품절)", "").strip()
				is_sale = fnc.IS_ELEMENT(obj_option.find("dd").find("del"))

				if is_sale == True :
					optprice = obj_option.find("dd").find("del").text.strip()
				else :
					optprice = obj_option.find("span", {"class" : "ff_rbt"}).text.strip()

				optprice = optprice.replace("원", "").replace(",", "")
				dic_option["title"] = optname
				dic_option["price"] = optprice

				arr_option.append(dic_option)
				#print(optname +" : "+ optprice)
		else :
			# 옵션이 없을때
			dic_option = {}
			
			opt = obj.select('.optionBoxList.1st')[0]
			optname = ''
			optprice = ''
			
			if fnc.IS_ELEMENT(opt.find('', {'class':'op_name'})) == True :
				optname = opt.select('.op_name')[0].text.strip()
				optname = optname.replace("[일시품절]", "").strip()
			else :
				optname = product_nm
			# end if
			if fnc.IS_ELEMENT(opt.find('', {'class':'op_pay'})) == True :
				optprice = opt.select('.op_pay')[0].text.strip()
				optprice = optprice.replace("원", "").replace(",", "")
			else :
				optprice = price
			# end if
			
			dic_option["title"] = optname
			dic_option["price"] = optprice
			arr_option.append(dic_option)
		#end if
		
		return arr_option
	#end try
	except Exception as err:
		logger.error("find error : "+ str(err))
	#end except
#end def


# 상품상세이미지 가져오기
def getDetailImg(obj, brand_cd) :
	try :
		list_desc_img = obj.find("", {"id" : "reN_infoP"}).find_all('img')

		arr_desc_img = []
		for ds_img in list_desc_img :
			img_url = ds_img.get("src")

			fullfilename = fnc.GET_FILENAME_FROM_URL(img_url)			# 전체파일명

			# fnc.MAKE_FOLDER(con._UPLOAD_DIR +'/'+ dsPrd['v_brandcd'])

			cust_filename = brand_cd +"/"+ fullfilename
			urllib.request.urlretrieve(img_url, con._UPLOAD_DIR +"/"+ cust_filename)		# 다운로드
			
			arr_desc_img.append(cust_filename)
		#end for

		return arr_desc_img
	#end try
	except Exception as err :
		logger.error("find error : "+ str(err))

		arr_desc_img = []
		return arr_desc_img
	#end except
#end def


# 상품가격 가져오기
def getPrice(obj) :
	try :
		obj_price = obj.find("div", {"class" : "nameR_pay"}).find("dl", {"class" : "base_pay"})
		is_sale = str(obj_price.find("dd", {"class" : "delT"}))
		
		if is_sale == "None" :
			price = obj_price.find("dd", {"class" : "baseT"}).find("b").text.strip()
		else :
			price = obj_price.find("dd", {"class" : "delT"}).find("del").text.strip()
		
		price = price[0 : len(price) - 1].replace(",", "")

		return price
	except Exception as err :
		logger.error("find error : "+ str(err))
	#end try
#end def


# 카테고리 가져오기
def getCategory(obj) :
	try :
		dic_cate = {}

		if fnc.IS_ELEMENT(obj.find('select', {'name':'i_sCategorynmEn1'})) == True :
			obj_option = obj.find('select', {'name':'i_sCategorynmEn1'}).find('option', {'selected':'selected'})
			cate_cd = obj_option.get('value')
			cate_nm = obj_option.text.strip()
			dic_cate['category_cd1'] = cate_cd
			dic_cate['category_nm1'] = cate_nm
		#end if
		if fnc.IS_ELEMENT(obj.find('select', {'name':'i_sCategorynmEn2'})) == True :
			obj_option = obj.find('select', {'name':'i_sCategorynmEn2'}).find('option', {'selected':'selected'})
			cate_cd = obj_option.get('value')
			cate_nm = obj_option.text.strip()
			dic_cate['category_cd2'] = cate_cd
			dic_cate['category_nm2'] = cate_nm
		#end if
		if fnc.IS_ELEMENT(obj.find('select', {'name':'i_sCategorynmEn3'})) == True :
			obj_option = obj.find('select', {'name':'i_sCategorynmEn3'}).find('option', {'selected':'selected'})
			cate_cd = obj_option.get('value')
			cate_nm = obj_option.text.strip()
			dic_cate['category_cd3'] = cate_cd
			dic_cate['category_nm3'] = cate_nm
		#end if
		

		return dic_cate
	except Exception as err :
		logger.error("find error : "+ str(err))
	#end try
#end def


# db select : get product info
def getProductInfo(p_cd) :
	try :
		# set db
		sql = cMysql.cMysql(con.DB_INFO)
		sql.db_conn()

		arParam = []
		qry = """
			SELECT * FROM TB_PRODUCT WHERE PRODUCT_CD = %s
		"""
		arParam.append(p_cd)
		result = sql.exec('data', qry, arParam)
		cnt = result['cnt']
		ds = result['data']

		return ds
	#end try
	except Exception as err :
		logger.error("find error : "+ str(err))
	#end except
	finally :
		sql.close()
	#end finally
#end def


# db select : get product info 2
def getProductInfo2(p_seq) :
	try :
		# set db
		sql = cMysql.cMysql(con.DB_INFO)
		sql.db_conn()

		arParam = []
		qry = """
			SELECT * FROM TB_PRODUCT WHERE PRODUCT_SEQ = %s
		"""
		arParam.append(p_seq)
		result = sql.exec('data', qry, arParam)
		cnt = result['cnt']
		ds = result['data']

		return ds
	#end try
	except Exception as err :
		logger.error("find error : "+ str(err))
	#end except
	finally :
		sql.close()
	#end finally
#end def


# db select : get option list
def getOptionList(product_seq) :
	try :
		# set db
		sql = cMysql.cMysql(con.DB_INFO)
		sql.db_conn()

		arParam = []
		qry = """
			SELECT * FROM TB_OPTION WHERE PRODUCT_SEQ = %s
		"""
		arParam.append(product_seq)
		result = sql.exec('list', qry, arParam)
		cnt = result['cnt']
		rs = result['data']

		return rs
	#end try
	except Exception as err :
		logger.error("find error : "+ str(err))
	#end except
	finally :
		sql.close()
	#end finally
#end def


# db select : get image list
def getImageList(product_seq) :
	try :
		# set db
		sql = cMysql.cMysql(con.DB_INFO)
		sql.db_conn()

		arParam = []
		qry = """
			SELECT * FROM TB_IMG WHERE PRODUCT_SEQ = %s
		"""
		arParam.append(product_seq)
		result = sql.exec('list', qry, arParam)
		cnt = result['cnt']
		rs = result['data']

		return rs
	#end try
	except Exception as err :
		logger.error("find error : "+ str(err))
	#end except
	finally :
		sql.close()
	#end finally
#end def


# db transaction : main information update
def setMainInfoInsert(arrMain) :
	try :
		# set db
		sql = cMysql.cMysql(con.DB_INFO)
		sql.db_conn()
		sql.tran()

		# set variables
		cate_cd1 = dic_info['category']['category_cd1']
		cate_cd2 = dic_info['category']['category_cd2']
		cate_cd3 = dic_info['category']['category_cd3']

		# make thumbnail
		thumb = setThumb(arrMain['main_img'], arrMain['brand_cd'])


		# product info insert
		arParam = []
		qry = """
			INSERT INTO `APMALL`.`TB_PRODUCT` (`PRODUCT_CD`,`PRODUCT_NM`,`PRODUCT_DESC`,`BRAND_CD`,`BRAND_NM`,`PRICE`,`CATE_CD1`,`CATE_CD2`,`CATE_CD3`,`THUMB1`,`REG_DT`)
			VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
		"""
		arParam.append(arrMain['product_cd'])
		arParam.append(arrMain['product_nm'])
		arParam.append(arrMain['product_desc'])
		arParam.append(arrMain['brand_cd'])
		arParam.append(arrMain['brand_nm'])
		arParam.append(arrMain['price'])
		arParam.append(cate_cd1)
		arParam.append(cate_cd2)
		arParam.append(cate_cd3)
		arParam.append(thumb)
		arParam.append(now_dt)
		result = sql.exec('insert', qry, arParam)
		cnt = result['cnt']
		product_seq = result['insertid']

		if cnt < 1 :
			raise Exception("상품정보저장 오류")
		#end if


		# main image insert
		list_main_img = arrMain["main_img"]
		param = []
		
		for main_img in list_main_img :
			arr = []
			arr.append(product_seq)
			arr.append(main_img)
			arr.append('M')
			arr.append(now_dt)
			param.append(arr)
		#end for

		qry = """
			INSERT INTO TB_IMG (PRODUCT_SEQ, FILE_NM, IMG_TP, REG_DT)
			VALUES(%s, %s, %s, %s)
		"""
		logger.debug(qry)
		logger.debug(str(param))
		result = sql.execmany(qry, param)
		cnt = result['cnt']

		if cnt < 1 :
			raise Exception("메인이미지 등록 카운트 오류")
		#end if


		# detail image insert
		list_desc_img = arrMain['desc_img']

		if len(list_desc_img) > 0 :
			param = []

			for desc_img in list_desc_img :
				arr = []
				arr.append(product_seq)
				arr.append(desc_img)
				arr.append('D')
				arr.append(now_dt)
				param.append(arr)
			#end for

			qry = """
				INSERT INTO TB_IMG (PRODUCT_SEQ, FILE_NM, IMG_TP, REG_DT)
				VALUES(%s, %s, %s, %s)
			"""
			logger.debug(qry)
			logger.debug(str(param))
			result = sql.execmany(qry, param)
			cnt = result['cnt']

			if cnt < 1 :
				raise Exception("상세이미지 등록 카운트 오류")
			#end if
		#end if

		# option insert
		list_option = arrMain["option"]

		if list_option != None :
			param = []
			
			for option in list_option :
				arr = []
				arr.append(product_seq)
				arr.append(option['title'])
				arr.append(option['price'])
				arr.append(now_dt)
				param.append(arr)
			#end for

			qry = """
				INSERT INTO TB_OPTION (PRODUCT_SEQ, OPT_NM, OPT_PRICE, REG_DT)
				VALUES(%s, %s, %s, %s)
			"""
			logger.debug(qry)
			logger.debug(str(param))
			result = sql.execmany(qry, param)
			cnt = result['cnt']

			if cnt < 1 :
				raise Exception("옵션정보 등록 카운트 오류")
			#end if
		#end if

		sql.commit()

		return product_seq
	#end try
	except Exception as err :
		sql.rollback()
		logger.error("find error : "+ str(err))
	#end except
	finally :
		sql.close()
	#end finally
#end def
