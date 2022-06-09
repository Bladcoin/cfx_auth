import {
	Conflux,
	format,
	address,
	Drip,
} from "js-conflux-sdk/dist/js-conflux-sdk.umd.min.js";
import { ethers, Contract} from 'ethers';
import { abi as posPoolAbi } from "../abi/IPoSPool.json";
import { abi as posManagerAbi } from "../abi/PoolManager.json";
import poolConfig from '../pool.config';

const space = localStorage.getItem('space') || 'core';
//const networkId = space === 'core' ? poolConfig.mainnet.core.networkId : poolConfig.mainnet.eSpace.networkId;
//const cfxUrl = space === 'core' ? poolConfig.mainnet.core.RPC : poolConfig.mainnet.eSpace.RPC;
const spaceProvider = new ethers.providers.JsonRpcProvider(poolConfig.mainnet.eSpace.RPC);

const conflux = new Conflux({
	url: poolConfig.mainnet.core.RPC,
	networkId: poolConfig.mainnet.core.networkId,
});

const confluxSpace = new Conflux({
	url: poolConfig.mainnet.eSpace.RPC,
	networkId: poolConfig.mainnet.eSpace.networkId,
});

const posPoolManagerAddress = poolConfig.mainnet.poolManagerAddress;

const getPosPoolContract = (address) =>
	 conflux.Contract({
		abi: posPoolAbi,
		address: address,
	})

const getSpaceContract = (address) =>
	new ethers.Contract(
		address,
		posPoolAbi,
		spaceProvider
	)

const posPoolManagerContract = conflux.Contract({
	abi: posManagerAbi,
	address: posPoolManagerAddress,
});

export const getPosAccountByPowAddress = async (address) => {
	//const posPoolContract = await getPosPoolContract(address);
	const posAddress = poolConfig.mainnet.posAddress;
	const posAccount = await conflux.provider.call("pos_getAccount", posAddress);
	return posAccount;
};
export {
	conflux,
	confluxSpace,
	format,
	address,
	Drip,
	getPosPoolContract,
	getSpaceContract,
	posPoolManagerContract,
	posPoolManagerAddress,
	spaceProvider,
};
