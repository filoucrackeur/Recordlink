.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _admin-manual:

Administrator Manual
====================

Reference TsConfig
^^^^^^^^^^^^^^^^^^

Define custom link type in wizard browser. An example would be ::

	TCEMAIN.linkHandler.record.configuration {
		category {
			label = Categories
			table = sys_category
			pid =
		}
	}

CHANGE NOTE ::

	mod.tx_recordlink and RTE.default.tx_recordlink don't work anymore, use TCEMAIN instead



Reference TypoScript
^^^^^^^^^^^^^^^^^^^^

Define custom typolink configuration. An example would be ::

	plugin.tx_recordlink {
		category {
			table = sys_category
			typolink {
				parameter.data = TSFE:id
				additionalParams = &sys_category={field:uid}
				additionalParams.insertData = 1
				useCacheHash = 1
			}
		}
	}

