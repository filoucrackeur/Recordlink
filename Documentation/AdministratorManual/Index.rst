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

	mod.tx_recordlink {
		category {
			label = Categories
			table = sys_category
			pid =
		}
	}

For RTE ::

	RTE.default.tx_recordlink.category < mod.tx_recordlink.category


For TYPO3 7.6::

    TCEMAIN.linkHandler.record.configuration < mod.tx_recordlink

Deprecation ::

    mod.tx_recordlink and RTE.default.tx_recordlink will be remove from version 1.2.0

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

