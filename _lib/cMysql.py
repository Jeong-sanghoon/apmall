# system lib
import pymysql

class cMysql :
	def __init__(self, db_info) :
		self.__db_info = db_info
		self.__conn = ''
		self.__sql = ''
		self.__autocommit = True

	# db connection
	def db_conn(self) :
		self.__conn = pymysql.connect(host=self.__db_info['host'], user=self.__db_info['user'], passwd=self.__db_info['passwd'], db=self.__db_info['db'], charset=self.__db_info['charset'])
		self.__sql = self.__conn.cursor(pymysql.cursors.DictCursor)


	# transaction
	def tran(self) :
		self.__autocommit = False


	# commit
	def commit(self) :
		self.__conn.commit()


	# rollback
	def rollback(self) :
		self.__conn.rollback()


	# query execute
	def exec(self, type, qry, param) :
		rtn = {}

		self.__sql.execute(qry, param)

		if type == 'list' :
			rtn['cnt'] = self.__sql.rowcount
			rtn['data'] = self.__sql.fetchall()
		elif type == 'data' :
			rtn['cnt'] = self.__sql.rowcount
			rtn['data'] = self.__sql.fetchone()
		elif type == 'insert' :
			rtn['cnt'] = self.__sql.rowcount
			rtn['insertid'] = self.__sql.lastrowid

			if self.__sql.rowcount > 1 :
				rtn['insertid'] = self.__sql.lastrowid + (self.__sql.rowcount - 1)

		elif type == 'update' :
			rtn['cnt'] = self.__sql.rowcount

		if self.__autocommit == True :
			self.__conn.commit()

		return rtn


	# query execute many : DML only
	def execmany(self, qry, param) :
		rtn = {}

		self.__sql.executemany(qry, param)

		rtn['cnt'] = self.__sql.rowcount
		rtn['insertid'] = self.__sql.lastrowid

		if self.__sql.rowcount > 1 :
			rtn['insertid'] = self.__sql.lastrowid + (self.__sql.rowcount - 1)

		if self.__autocommit == True :
			self.__conn.commit()

		return rtn


	# db close
	def close(self) :
		self.__sql.close()
		self.__conn.close()
